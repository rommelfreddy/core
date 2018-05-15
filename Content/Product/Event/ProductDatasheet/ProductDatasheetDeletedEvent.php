<?php declare(strict_types=1);

namespace Shopware\Content\Product\Event\ProductDatasheet;

use Shopware\Framework\ORM\Write\DeletedEvent;
use Shopware\Framework\ORM\Write\WrittenEvent;
use Shopware\Content\Product\Definition\ProductDatasheetDefinition;

class ProductDatasheetDeletedEvent extends WrittenEvent implements DeletedEvent
{
    public const NAME = 'product_datasheet.deleted';

    public function getName(): string
    {
        return self::NAME;
    }

    public function getDefinition(): string
    {
        return ProductDatasheetDefinition::class;
    }
}
