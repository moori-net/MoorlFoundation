<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\Cms\CmsBrandslider;

use Shopware\Core\Content\Product\Aggregate\ProductManufacturer\ProductManufacturerCollection;
use Shopware\Core\Framework\Struct\Struct;

class AppflixBrandSliderStruct extends Struct
{
    /**
     * @var ProductCollection|null
     */
    protected $brands;

    public function getBrands(): ?ProductManufacturerCollection
    {
        return $this->brands;
    }

    public function setBrands(ProductManufacturerCollection $brands): void
    {
        $this->brands = $brands;
    }
}
