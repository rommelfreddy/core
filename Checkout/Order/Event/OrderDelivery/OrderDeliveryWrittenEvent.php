<?php declare(strict_types=1);

namespace Shopware\Checkout\Order\Event\OrderDelivery;

use Shopware\Framework\ORM\Write\WrittenEvent;
use Shopware\Checkout\Order\Definition\OrderDeliveryDefinition;

class OrderDeliveryWrittenEvent extends WrittenEvent
{
    public const NAME = 'order_delivery.written';

    public function getName(): string
    {
        return self::NAME;
    }

    public function getDefinition(): string
    {
        return OrderDeliveryDefinition::class;
    }
}
