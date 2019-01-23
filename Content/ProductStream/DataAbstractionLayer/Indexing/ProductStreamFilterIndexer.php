<?php declare(strict_types=1);

namespace Shopware\Core\Content\ProductStream\DataAbstractionLayer\Indexing;

use Doctrine\DBAL\Connection;
use Shopware\Core\Content\ConditionTree\DataAbstractionLayer\Indexing\EventIdExtractorInterface;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Content\ProductStream\ProductStreamDefinition;
use Shopware\Core\Content\Rule\Exception\UnsupportedOperatorException;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Cache\EntityCacheKeyGenerator;
use Shopware\Core\Framework\DataAbstractionLayer\Dbal\Common\RepositoryIterator;
use Shopware\Core\Framework\DataAbstractionLayer\Dbal\Indexing\IndexerInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenContainerEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InvalidFilterQueryException;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\SearchRequestException;
use Shopware\Core\Framework\DataAbstractionLayer\RepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Parser\QueryStringParser;
use Shopware\Core\Framework\Doctrine\FetchModeHelper;
use Shopware\Core\Framework\Event\ProgressAdvancedEvent;
use Shopware\Core\Framework\Event\ProgressFinishedEvent;
use Shopware\Core\Framework\Event\ProgressStartedEvent;
use Shopware\Core\Framework\Struct\Uuid;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Serializer\Serializer;

class ProductStreamFilterIndexer implements IndexerInterface
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var EntityCacheKeyGenerator
     */
    private $cacheKeyGenerator;

    /**
     * @var TagAwareAdapter
     */
    private $cache;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var EventIdExtractorInterface
     */
    private $eventIdExtractor;

    /**
     * @var RepositoryInterface
     */
    private $repository;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        EventIdExtractorInterface $eventIdExtractor,
        RepositoryInterface $repository,
        Connection $connection,
        Serializer $serializer,
        EntityCacheKeyGenerator $cacheKeyGenerator,
        TagAwareAdapter $cache
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->eventIdExtractor = $eventIdExtractor;
        $this->repository = $repository;
        $this->connection = $connection;
        $this->serializer = $serializer;
        $this->cacheKeyGenerator = $cacheKeyGenerator;
        $this->cache = $cache;
    }

    public function index(\DateTime $timestamp): void
    {
        $context = Context::createDefaultContext();

        $iterator = $this->createIterator($context);

        $this->eventDispatcher->dispatch(
            ProgressStartedEvent::NAME,
            new ProgressStartedEvent('Start indexing product streams', $iterator->getTotal())
        );

        while ($ids = $iterator->fetchIds()) {
            $this->update($ids);
            $this->eventDispatcher->dispatch(
                ProgressAdvancedEvent::NAME,
                new ProgressAdvancedEvent(\count($ids))
            );
        }

        $this->eventDispatcher->dispatch(
            ProgressFinishedEvent::NAME,
            new ProgressFinishedEvent('Finished indexing product streams')
        );
    }

    public function refresh(EntityWrittenContainerEvent $event): void
    {
        $ids = $this->eventIdExtractor->getEntityIds($event);
        $this->update($ids);
    }

    protected function update(array $ids): void
    {
        if (empty($ids)) {
            return;
        }

        if ($this->cache->hasItem('product_streams_key')) {
            $this->cache->deleteItem('product_streams_key');
        }

        $bytes = array_values(array_map(function ($id) { return Uuid::fromHexToBytes($id); }, $ids));

        $filters = $this->connection->fetchAll(
            'SELECT psc.product_stream_id as array_key, psc.* FROM product_stream_filter psc  WHERE psc.product_stream_id IN (:ids) ORDER BY psc.product_stream_id',
            ['ids' => $bytes],
            ['ids' => Connection::PARAM_STR_ARRAY]
        );

        $filters = FetchModeHelper::group($filters);

        $tags = [];
        foreach ($filters as $id => $filter) {
            $invalid = false;
            $serialized = null;
            try {
                $nested = $this->buildNested($filter, null);

                $searchException = new SearchRequestException();
                $streamFilter = [];

                foreach ($nested as $value) {
                    $parsed = QueryStringParser::fromArray(ProductDefinition::class, $value, $searchException);
                    $streamFilter[] = QueryStringParser::toArray($parsed);
                }

                if ($searchException->getErrors()->current()) {
                    throw $searchException;
                }

                $tags[] = $this->cacheKeyGenerator->getEntityTag(Uuid::fromBytesToHex($id), ProductStreamDefinition::class);

                $serialized = $this->serializer->serialize($streamFilter, 'json');
            } catch (InvalidFilterQueryException | SearchRequestException $exception) {
                $invalid = true;
            } finally {
                $this->connection->createQueryBuilder()
                    ->update('product_stream')
                    ->set('filter', ':serialize')
                    ->set('invalid', ':invalid')
                    ->where('id = :id')
                    ->setParameter('id', $id)
                    ->setParameter('serialize', $serialized)
                    ->setParameter('invalid', (int) $invalid)
                    ->execute();
            }
        }

        $this->cache->invalidateTags($tags);
    }

    /**
     * @throws UnsupportedOperatorException
     */
    protected function buildNested(array $entities, ?string $parentId): array
    {
        $nested = [];
        foreach ($entities as $entity) {
            if ($entity['parent_id'] !== $parentId) {
                continue;
            }

            if ($this->isJsonString($entity['parameters'])) {
                $entity['parameters'] = json_decode($entity['parameters'], true);
            }

            if ($this->isMultiFilter($entity['type'])) {
                $entity['queries'] = $this->buildNested($entities, $entity['id']);
            }

            $nested[] = $entity;
        }

        return $nested;
    }

    private function isJsonString($string): bool
    {
        if (!$string || !is_string($string)) {
            return false;
        }

        json_decode($string);

        return json_last_error() == JSON_ERROR_NONE;
    }

    private function createIterator(Context $context)
    {
        return new RepositoryIterator($this->repository, $context);
    }

    private function isMultiFilter(string $type): bool
    {
        return in_array($type, ['multi', 'not']);
    }
}
