<?php declare(strict_types=1);

namespace Shopware\Content\Product\Event\ProductContextPrice;

use Shopware\Framework\ORM\Write\DeletedEvent;
use Shopware\Framework\ORM\Write\WrittenEvent;
use Shopware\Content\Product\Definition\ProductContextPriceDefinition;

class ProductContextPriceDeletedEvent extends WrittenEvent implements DeletedEvent
{
    public const NAME = 'product_context_price.deleted';

    public function getName(): string
    {
        return self::NAME;
    }

    public function getDefinition(): string
    {
        return ProductContextPriceDefinition::class;
    }
}
