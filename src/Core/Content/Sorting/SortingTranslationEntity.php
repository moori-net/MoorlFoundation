<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\Sorting;

use Shopware\Core\Content\Product\SalesChannel\Sorting\ProductSortingTranslationEntity;

class SortingTranslationEntity extends ProductSortingTranslationEntity
{
    public function getApiAlias(): string
    {
        return 'moorl_sorting_translation';
    }
}
