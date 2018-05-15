<?php declare(strict_types=1);

namespace Shopware\System\Country\Event\Country;

use Shopware\System\Country\Definition\CountryDefinition;
use Shopware\Framework\ORM\Write\DeletedEvent;
use Shopware\Framework\ORM\Write\WrittenEvent;

class CountryDeletedEvent extends WrittenEvent implements DeletedEvent
{
    public const NAME = 'country.deleted';

    public function getName(): string
    {
        return self::NAME;
    }

    public function getDefinition(): string
    {
        return CountryDefinition::class;
    }
}
