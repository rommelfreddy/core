<?php declare(strict_types=1);

namespace Shopware\Api\Shop\Event\ShopTemplateConfigPreset;

use Shopware\Framework\ORM\Write\DeletedEvent;
use Shopware\Framework\ORM\Write\WrittenEvent;
use Shopware\Api\Shop\Definition\ShopTemplateConfigPresetDefinition;

class ShopTemplateConfigPresetDeletedEvent extends WrittenEvent implements DeletedEvent
{
    public const NAME = 'shop_template_config_preset.deleted';

    public function getName(): string
    {
        return self::NAME;
    }

    public function getDefinition(): string
    {
        return ShopTemplateConfigPresetDefinition::class;
    }
}
