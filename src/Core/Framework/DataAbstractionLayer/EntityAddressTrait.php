<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer;

use Shopware\Core\System\Country\Aggregate\CountryState\CountryStateEntity;
use Shopware\Core\System\Country\CountryEntity;

trait EntityAddressTrait
{
    protected ?string $zipcode = null;
    protected ?string $city = null;
    protected ?string $street = null;
    protected ?string $streetNumber = null;
    protected ?string $additionalAddressLine1 = null;
    protected ?string $additionalAddressLine2 = null;
    protected ?string $countryCode = null;
    protected ?string $countryId = null;
    protected ?string $countryStateId = null;
    protected ?CountryEntity $country = null;
    protected ?CountryStateEntity $countryState = null;
    protected ?string $locationPlaceId = null;

    public function getLocationPlaceId(): ?string
    {
        return $this->locationPlaceId;
    }

    public function setLocationPlaceId(?string $locationPlaceId): void
    {
        $this->locationPlaceId = $locationPlaceId;
    }

    public function getCountryCode(): ?string
    {
        return $this->countryCode;
    }

    public function setCountryCode(?string $countryCode): void
    {
        $this->countryCode = $countryCode;
    }

    public function getZipcode(): ?string
    {
        return $this->zipcode;
    }

    public function setZipcode(?string $zipcode): void
    {
        $this->zipcode = $zipcode;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): void
    {
        $this->city = $city;
    }

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(?string $street): void
    {
        $this->street = $street;
    }

    public function getStreetNumber(): ?string
    {
        return $this->streetNumber;
    }

    public function setStreetNumber(?string $streetNumber): void
    {
        $this->streetNumber = $streetNumber;
    }

    public function getAdditionalAddressLine1(): ?string
    {
        return $this->additionalAddressLine1;
    }

    public function setAdditionalAddressLine1(?string $additionalAddressLine1): void
    {
        $this->additionalAddressLine1 = $additionalAddressLine1;
    }

    public function getAdditionalAddressLine2(): ?string
    {
        return $this->additionalAddressLine2;
    }

    public function setAdditionalAddressLine2(?string $additionalAddressLine2): void
    {
        $this->additionalAddressLine2 = $additionalAddressLine2;
    }

    public function getCountryId(): ?string
    {
        return $this->countryId;
    }

    public function setCountryId(?string $countryId): void
    {
        $this->countryId = $countryId;
    }

    public function getCountryStateId(): ?string
    {
        return $this->countryStateId;
    }

    public function setCountryStateId(?string $countryStateId): void
    {
        $this->countryStateId = $countryStateId;
    }

    public function getCountry(): ?CountryEntity
    {
        return $this->country;
    }

    public function setCountry(?CountryEntity $country): void
    {
        $this->country = $country;
    }

    public function getCountryState(): ?CountryStateEntity
    {
        return $this->countryState;
    }

    public function setCountryState(?CountryStateEntity $countryState): void
    {
        $this->countryState = $countryState;
    }
}
