<?php declare(strict_types=1);

namespace Shopware\System\Listing\Struct;

use Shopware\Framework\ORM\Search\SearchResultInterface;
use Shopware\Framework\ORM\Search\SearchResultTrait;
use Shopware\System\Listing\Collection\ListingSortingTranslationBasicCollection;

class ListingSortingTranslationSearchResult extends ListingSortingTranslationBasicCollection implements SearchResultInterface
{
    use SearchResultTrait;
}
