<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer;

use Shopware\Core\Content\Product\Aggregate\ProductManufacturer\ProductManufacturerEntity;

trait EntityProductManufacturerTrait
{
    protected ?string $productManufacturerId = null;
    protected ?ProductManufacturerEntity $productManufacturer = null;

    public function getProductManufacturerId(): ?string
    {
        return $this->productManufacturerId;
    }

    public function setProductManufacturerId(?string $productManufacturerId): void
    {
        $this->productManufacturerId = $productManufacturerId;
    }

    public function getProductManufacturer(): ?ProductManufacturerEntity
    {
        return $this->productManufacturer;
    }

    public function setProductManufacturer(?ProductManufacturerEntity $productManufacturer): void
    {
        $this->productManufacturer = $productManufacturer;
    }
}
