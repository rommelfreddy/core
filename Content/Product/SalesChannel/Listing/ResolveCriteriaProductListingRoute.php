<?php declare(strict_types=1);

namespace Shopware\Core\Content\Product\SalesChannel\Listing;

use Shopware\Core\Content\Product\Events\ProductListingCriteriaEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Routing\Annotation\Since;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @package inventory
 */
#[Route(defaults: ['_routeScope' => ['store-api']])]
class ResolveCriteriaProductListingRoute extends AbstractProductListingRoute
{
    /**
     * @internal
     */
    public function __construct(private readonly AbstractProductListingRoute $decorated, private readonly EventDispatcherInterface $eventDispatcher)
    {
    }

    public function getDecorated(): AbstractProductListingRoute
    {
        return $this->decorated;
    }

    /**
     * @Since("6.2.0.0")
     */
    #[Route(path: '/store-api/product-listing/{categoryId}', name: 'store-api.product.listing', methods: ['POST'], defaults: ['_entity' => 'product'])]
    public function load(string $categoryId, Request $request, SalesChannelContext $context, Criteria $criteria): ProductListingRouteResponse
    {
        $this->eventDispatcher->dispatch(
            new ProductListingCriteriaEvent($request, $criteria, $context)
        );

        return $this->getDecorated()->load($categoryId, $request, $context, $criteria);
    }
}
