<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\CartCombinationDiscount;

use Shopware\Core\Framework\DataAbstractionLayer\TranslationEntity;

class CartCombinationDiscountTranslationEntity extends TranslationEntity
{
    protected string $name;
    protected ?string $description = null;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }
}
