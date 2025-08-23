<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\Sorting;

use Shopware\Core\Content\Product\SalesChannel\Sorting\ProductSortingTranslationEntity;

class SortingTranslationEntity extends ProductSortingTranslationEntity
{
    protected ?string $label = null;

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(?string $label): void
    {
        $this->label = $label;
    }

    public function getApiAlias(): string
    {
        return 'moorl_sorting_translation';
    }
}
