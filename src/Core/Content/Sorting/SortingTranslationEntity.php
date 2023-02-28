<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\Sorting;

use Shopware\Core\Content\Product\SalesChannel\Sorting\ProductSortingTranslationEntity;

class SortingTranslationEntity extends ProductSortingTranslationEntity
{
    protected string $moorlSortingId;
    protected SortingEntity $moorlSorting;

    public function getMoorlSortingId(): string
    {
        return $this->moorlSortingId;
    }

    public function setMoorlSortingId(string $moorlSortingId): void
    {
        $this->moorlSortingId = $moorlSortingId;
    }

    public function getMoorlSorting(): SortingEntity
    {
        return $this->moorlSorting;
    }

    public function setMoorlSorting(SortingEntity $moorlSorting): void
    {
        $this->moorlSorting = $moorlSorting;
    }

    public function getApiAlias(): string
    {
        return 'moorl_sorting_translation';
    }
}
