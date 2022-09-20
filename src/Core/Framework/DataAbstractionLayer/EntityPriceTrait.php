<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer;

use Shopware\Core\Framework\DataAbstractionLayer\Pricing\PriceCollection;
use Shopware\Core\System\Tax\TaxEntity;

trait EntityPriceTrait
{
    protected string $taxId;
    protected ?TaxEntity $tax = null;
    protected ?PriceCollection $price = null;

    /**
     * @param string $taxId
     */
    public function setTaxId(string $taxId): void
    {
        $this->taxId = $taxId;
    }

    /**
     * @return string
     */
    public function getTaxId(): string
    {
        return $this->taxId;
    }

    /**
     * @param TaxEntity|null $tax
     */
    public function setTax(?TaxEntity $tax): void
    {
        $this->tax = $tax;
    }

    /**
     * @return TaxEntity|null
     */
    public function getTax(): ?TaxEntity
    {
        return $this->tax;
    }

    /**
     * @param PriceCollection|null $price
     */
    public function setPrice(?PriceCollection $price): void
    {
        $this->price = $price;
    }

    /**
     * @return PriceCollection|null
     */
    public function getPrice(): ?PriceCollection
    {
        return $this->price;
    }
}
