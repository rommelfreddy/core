<?php declare(strict_types=1);

namespace Shopware\Content\Product\Event\ProductConfigurator;

use Shopware\Framework\ORM\Write\DeletedEvent;
use Shopware\Framework\ORM\Write\WrittenEvent;
use Shopware\Content\Product\Definition\ProductConfiguratorDefinition;

class ProductConfiguratorDeletedEvent extends WrittenEvent implements DeletedEvent
{
    public const NAME = 'product_configurator.deleted';

    public function getName(): string
    {
        return self::NAME;
    }

    public function getDefinition(): string
    {
        return ProductConfiguratorDefinition::class;
    }
}
