<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer;

trait EntityContactTrait
{
    protected ?string $email = null;
    protected ?string $phoneNumber = null;
    protected ?string $shopUrl = null;
    protected ?string $merchantUrl = null;

    public function getShopUrl(): ?string
    {
        return $this->shopUrl;
    }

    public function setShopUrl(?string $shopUrl): void
    {
        $this->shopUrl = $shopUrl;
    }

    public function getMerchantUrl(): ?string
    {
        return $this->merchantUrl;
    }

    public function setMerchantUrl(?string $merchantUrl): void
    {
        $this->merchantUrl = $merchantUrl;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(?string $phoneNumber): void
    {
        $this->phoneNumber = $phoneNumber;
    }
}
