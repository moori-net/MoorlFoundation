<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer;

trait EntityCompanyTrait
{
    protected ?string $executiveDirector = null;
    protected ?string $placeOfFulfillment = null;
    protected ?string $placeOfJurisdiction = null;
    protected ?string $bankBic = null;
    protected ?string $bankIban = null;
    protected ?string $bankName = null;
    protected ?string $taxOffice = null;
    protected ?string $taxNumber = null;
    protected ?string $vatId = null;

    public function getExecutiveDirector(): ?string
    {
        return $this->executiveDirector;
    }

    public function setExecutiveDirector(?string $executiveDirector): void
    {
        $this->executiveDirector = $executiveDirector;
    }

    public function getPlaceOfFulfillment(): ?string
    {
        return $this->placeOfFulfillment;
    }

    public function setPlaceOfFulfillment(?string $placeOfFulfillment): void
    {
        $this->placeOfFulfillment = $placeOfFulfillment;
    }

    public function getPlaceOfJurisdiction(): ?string
    {
        return $this->placeOfJurisdiction;
    }

    public function setPlaceOfJurisdiction(?string $placeOfJurisdiction): void
    {
        $this->placeOfJurisdiction = $placeOfJurisdiction;
    }

    public function getBankBic(): ?string
    {
        return $this->bankBic;
    }

    public function setBankBic(?string $bankBic): void
    {
        $this->bankBic = $bankBic;
    }

    public function getBankIban(): ?string
    {
        return $this->bankIban;
    }

    public function setBankIban(?string $bankIban): void
    {
        $this->bankIban = $bankIban;
    }

    public function getBankName(): ?string
    {
        return $this->bankName;
    }

    public function setBankName(?string $bankName): void
    {
        $this->bankName = $bankName;
    }

    public function getTaxOffice(): ?string
    {
        return $this->taxOffice;
    }

    public function setTaxOffice(?string $taxOffice): void
    {
        $this->taxOffice = $taxOffice;
    }

    public function getTaxNumber(): ?string
    {
        return $this->taxNumber;
    }

    public function setTaxNumber(?string $taxNumber): void
    {
        $this->taxNumber = $taxNumber;
    }

    public function getVatId(): ?string
    {
        return $this->vatId;
    }

    public function setVatId(?string $vatId): void
    {
        $this->vatId = $vatId;
    }
}
