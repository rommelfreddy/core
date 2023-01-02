<?php declare(strict_types=1);

namespace Shopware\Core\Content\Newsletter\Event;

use Shopware\Core\Content\Flow\Dispatching\Aware\NewsletterRecipientAware;
use Shopware\Core\Content\Newsletter\Aggregate\NewsletterRecipient\NewsletterRecipientDefinition;
use Shopware\Core\Content\Newsletter\Aggregate\NewsletterRecipient\NewsletterRecipientEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Event\EventData\EntityType;
use Shopware\Core\Framework\Event\EventData\EventDataCollection;
use Shopware\Core\Framework\Event\EventData\MailRecipientStruct;
use Shopware\Core\Framework\Event\MailAware;
use Shopware\Core\Framework\Event\SalesChannelAware;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Struct\JsonSerializableTrait;
use Symfony\Contracts\EventDispatcher\Event;

#[Package('customer-order')]
class NewsletterConfirmEvent extends Event implements SalesChannelAware, MailAware, NewsletterRecipientAware
{
    use JsonSerializableTrait;

    public const EVENT_NAME = 'newsletter.confirm';

    /**
     * @var Context
     */
    private $context;

    /**
     * @var NewsletterRecipientEntity
     */
    private $newsletterRecipient;

    /**
     * @var MailRecipientStruct|null
     */
    private $mailRecipientStruct;

    /**
     * @var string
     */
    private $salesChannelId;

    public function __construct(Context $context, NewsletterRecipientEntity $newsletterRecipient, string $salesChannelId)
    {
        $this->context = $context;
        $this->newsletterRecipient = $newsletterRecipient;
        $this->salesChannelId = $salesChannelId;
    }

    public function getName(): string
    {
        return self::EVENT_NAME;
    }

    public function getContext(): Context
    {
        return $this->context;
    }

    public static function getAvailableData(): EventDataCollection
    {
        return (new EventDataCollection())
            ->add('newsletterRecipient', new EntityType(NewsletterRecipientDefinition::class));
    }

    public function getNewsletterRecipient(): NewsletterRecipientEntity
    {
        return $this->newsletterRecipient;
    }

    public function getMailStruct(): MailRecipientStruct
    {
        if (!$this->mailRecipientStruct instanceof MailRecipientStruct) {
            $this->mailRecipientStruct = new MailRecipientStruct([
                $this->newsletterRecipient->getEmail() => $this->newsletterRecipient->getFirstName() . ' ' . $this->newsletterRecipient->getLastName(),
            ]);
        }

        return $this->mailRecipientStruct;
    }

    public function getSalesChannelId(): string
    {
        return $this->salesChannelId;
    }

    public function getNewsletterRecipientId(): string
    {
        return $this->newsletterRecipient->getId();
    }
}
