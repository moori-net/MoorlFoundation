<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer;

use Shopware\Core\Framework\DataAbstractionLayer\Pricing\PriceCollection;
use Shopware\Core\System\Tax\TaxEntity;

trait EntityPriceTrait
{
    protected string $taxId;
    protected ?TaxEntity $tax = null;
    protected ?PriceCollection $price = null;

    public function setTaxId(string $taxId): void
    {
        $this->taxId = $taxId;
    }

    public function getTaxId(): string
    {
        return $this->taxId;
    }

    public function setTax(?TaxEntity $tax): void
    {
        $this->tax = $tax;
    }

    public function getTax(): ?TaxEntity
    {
        return $this->tax;
    }

    public function setPrice(?PriceCollection $price): void
    {
        $this->price = $price;
    }

    public function getPrice(): ?PriceCollection
    {
        return $this->price;
    }
}
