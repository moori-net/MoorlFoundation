<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer;

trait EntityContactTrait
{
    protected ?string $email = null;
    protected ?string $phoneNumber = null;
    protected ?string $shopUrl = null;
    protected ?string $merchantUrl = null;

    /**
     * @return string|null
     */
    public function getShopUrl(): ?string
    {
        return $this->shopUrl;
    }

    /**
     * @param string|null $shopUrl
     */
    public function setShopUrl(?string $shopUrl): void
    {
        $this->shopUrl = $shopUrl;
    }

    /**
     * @return string|null
     */
    public function getMerchantUrl(): ?string
    {
        return $this->merchantUrl;
    }

    /**
     * @param string|null $merchantUrl
     */
    public function setMerchantUrl(?string $merchantUrl): void
    {
        $this->merchantUrl = $merchantUrl;
    }

    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string|null $email
     */
    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    /**
     * @return string|null
     */
    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    /**
     * @param string|null $phoneNumber
     */
    public function setPhoneNumber(?string $phoneNumber): void
    {
        $this->phoneNumber = $phoneNumber;
    }
}
