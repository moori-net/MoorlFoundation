<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer;

use MoorlFoundation\Core\Service\PriceCalculatorService;
use Shopware\Core\Framework\DataAbstractionLayer\Pricing\PriceCollection;

trait EntityPriceCalculationTrait
{
    protected bool $showDiscount = false;
    protected string $optionType = PriceCalculatorService::TYPE_PERCENTAGE;
    protected string $calculationPriceSource = PriceCalculatorService::SOURCE_ORIGIN_PRICE;
    protected string $listPriceSource = PriceCalculatorService::SOURCE_ORIGIN_LIST_PRICE;
    protected float $optionPercentage = 0.00;
    protected ?PriceCollection $optionPrice = null;

    public function getShowDiscount(): bool
    {
        return $this->showDiscount;
    }

    public function setShowDiscount(bool $showDiscount): void
    {
        $this->showDiscount = $showDiscount;
    }

    public function getOptionType(): string
    {
        return $this->optionType;
    }

    public function setOptionType(string $optionType): void
    {
        $this->optionType = $optionType;
    }

    public function getCalculationPriceSource(): string
    {
        return $this->calculationPriceSource;
    }

    public function setCalculationPriceSource(string $calculationPriceSource): void
    {
        $this->calculationPriceSource = $calculationPriceSource;
    }

    public function getListPriceSource(): string
    {
        return $this->listPriceSource;
    }

    public function setListPriceSource(string $listPriceSource): void
    {
        $this->listPriceSource = $listPriceSource;
    }

    public function getOptionPercentage(): float
    {
        return $this->optionPercentage;
    }

    public function setOptionPercentage(float $optionPercentage): void
    {
        $this->optionPercentage = $optionPercentage;
    }

    public function getOptionPrice(): ?PriceCollection
    {
        return $this->optionPrice;
    }

    public function setOptionPrice(?PriceCollection $optionPrice): void
    {
        $this->optionPrice = $optionPrice;
    }
}
