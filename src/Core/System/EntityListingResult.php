<?php declare(strict_types=1);

namespace MoorlFoundation\Core\System;

use Shopware\Core\Content\Product\SalesChannel\Listing\ProductListingResult;

class EntityListingResult extends ProductListingResult
{
    protected array $elementConfig = [];

    public function getElementConfig(): array
    {
        return $this->elementConfig;
    }

    public function setElementConfig(array $elementConfig): void
    {
        $this->elementConfig = $elementConfig;
    }
}
