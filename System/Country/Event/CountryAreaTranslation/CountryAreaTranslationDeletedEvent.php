<?php declare(strict_types=1);

namespace Shopware\System\Country\Event\CountryAreaTranslation;

use Shopware\System\Country\Definition\CountryAreaTranslationDefinition;
use Shopware\Framework\ORM\Write\DeletedEvent;
use Shopware\Framework\ORM\Write\WrittenEvent;

class CountryAreaTranslationDeletedEvent extends WrittenEvent implements DeletedEvent
{
    public const NAME = 'country_area_translation.deleted';

    public function getName(): string
    {
        return self::NAME;
    }

    public function getDefinition(): string
    {
        return CountryAreaTranslationDefinition::class;
    }
}
