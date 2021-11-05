<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\Sorting;

use Shopware\Core\Content\Product\SalesChannel\Sorting\ProductSortingEntity;

class SortingEntity extends ProductSortingEntity
{
    public function getApiAlias(): string
    {
        return 'moorl_sorting';
    }
}
