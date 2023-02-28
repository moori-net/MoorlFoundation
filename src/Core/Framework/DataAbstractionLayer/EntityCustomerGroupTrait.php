<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer;

use Shopware\Core\Checkout\Customer\Aggregate\CustomerGroup\CustomerGroupEntity;

trait EntityCustomerGroupTrait
{
    protected ?string $customerGroupId = null;
    protected ?CustomerGroupEntity $customerGroup = null;

    public function getCustomerGroupId(): ?string
    {
        return $this->customerGroupId;
    }

    public function setCustomerGroupId(?string $customerGroupId): void
    {
        $this->customerGroupId = $customerGroupId;
    }

    public function getCustomerGroup(): ?CustomerGroupEntity
    {
        return $this->customerGroup;
    }

    public function setCustomerGroup(?CustomerGroupEntity $customerGroup): void
    {
        $this->customerGroup = $customerGroup;
    }
}
