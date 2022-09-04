<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer;

use Shopware\Core\System\Salutation\SalutationEntity;

trait EntityPersonTrait
{
    protected ?string $salutationId = null;
    protected ?string $firstName = null;
    protected ?string $lastName = null;
    protected ?string $title = null;
    protected ?string $company = null;
    protected ?SalutationEntity $salutation = null;

    /**
     * @return string|null
     */
    public function getSalutationId(): ?string
    {
        return $this->salutationId;
    }

    /**
     * @param string|null $salutationId
     */
    public function setSalutationId(?string $salutationId): void
    {
        $this->salutationId = $salutationId;
    }

    /**
     * @return string|null
     */
    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    /**
     * @param string|null $firstName
     */
    public function setFirstName(?string $firstName): void
    {
        $this->firstName = $firstName;
    }

    /**
     * @return string|null
     */
    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    /**
     * @param string|null $lastName
     */
    public function setLastName(?string $lastName): void
    {
        $this->lastName = $lastName;
    }

    /**
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param string|null $title
     */
    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return string|null
     */
    public function getCompany(): ?string
    {
        return $this->company;
    }

    /**
     * @param string|null $company
     */
    public function setCompany(?string $company): void
    {
        $this->company = $company;
    }

    /**
     * @return SalutationEntity|null
     */
    public function getSalutation(): ?SalutationEntity
    {
        return $this->salutation;
    }

    /**
     * @param SalutationEntity|null $salutation
     */
    public function setSalutation(?SalutationEntity $salutation): void
    {
        $this->salutation = $salutation;
    }
}
