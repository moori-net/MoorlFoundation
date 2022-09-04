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

    /**
     * @return string|null
     */
    public function getExecutiveDirector(): ?string
    {
        return $this->executiveDirector;
    }

    /**
     * @param string|null $executiveDirector
     */
    public function setExecutiveDirector(?string $executiveDirector): void
    {
        $this->executiveDirector = $executiveDirector;
    }

    /**
     * @return string|null
     */
    public function getPlaceOfFulfillment(): ?string
    {
        return $this->placeOfFulfillment;
    }

    /**
     * @param string|null $placeOfFulfillment
     */
    public function setPlaceOfFulfillment(?string $placeOfFulfillment): void
    {
        $this->placeOfFulfillment = $placeOfFulfillment;
    }

    /**
     * @return string|null
     */
    public function getPlaceOfJurisdiction(): ?string
    {
        return $this->placeOfJurisdiction;
    }

    /**
     * @param string|null $placeOfJurisdiction
     */
    public function setPlaceOfJurisdiction(?string $placeOfJurisdiction): void
    {
        $this->placeOfJurisdiction = $placeOfJurisdiction;
    }

    /**
     * @return string|null
     */
    public function getBankBic(): ?string
    {
        return $this->bankBic;
    }

    /**
     * @param string|null $bankBic
     */
    public function setBankBic(?string $bankBic): void
    {
        $this->bankBic = $bankBic;
    }

    /**
     * @return string|null
     */
    public function getBankIban(): ?string
    {
        return $this->bankIban;
    }

    /**
     * @param string|null $bankIban
     */
    public function setBankIban(?string $bankIban): void
    {
        $this->bankIban = $bankIban;
    }

    /**
     * @return string|null
     */
    public function getBankName(): ?string
    {
        return $this->bankName;
    }

    /**
     * @param string|null $bankName
     */
    public function setBankName(?string $bankName): void
    {
        $this->bankName = $bankName;
    }

    /**
     * @return string|null
     */
    public function getTaxOffice(): ?string
    {
        return $this->taxOffice;
    }

    /**
     * @param string|null $taxOffice
     */
    public function setTaxOffice(?string $taxOffice): void
    {
        $this->taxOffice = $taxOffice;
    }

    /**
     * @return string|null
     */
    public function getTaxNumber(): ?string
    {
        return $this->taxNumber;
    }

    /**
     * @param string|null $taxNumber
     */
    public function setTaxNumber(?string $taxNumber): void
    {
        $this->taxNumber = $taxNumber;
    }

    /**
     * @return string|null
     */
    public function getVatId(): ?string
    {
        return $this->vatId;
    }

    /**
     * @param string|null $vatId
     */
    public function setVatId(?string $vatId): void
    {
        $this->vatId = $vatId;
    }
}
