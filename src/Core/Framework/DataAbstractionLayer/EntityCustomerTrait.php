<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer;

use Shopware\Core\Checkout\Customer\CustomerEntity;

trait EntityCustomerTrait
{
    protected ?string $customerId = null;
    protected ?CustomerEntity $customer = null;

    public function setCustomerId(?string $customerId): void
    {
        $this->customerId = $customerId;
    }

    public function getCustomerId(): ?string
    {
        return $this->customerId;
    }

    public function setCustomer(?CustomerEntity $customer): void
    {
        $this->customer = $customer;
    }

    public function getCustomer(): ?CustomerEntity
    {
        return $this->customer;
    }
}
