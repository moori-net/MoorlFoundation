<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\CartCombinationDiscount;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class CartCombinationDiscountItemEntity extends Entity
{
    use EntityIdTrait;

    protected string $CartCombinationDiscountId;
    protected ?string $productStreamId = null;
    protected ?string $productId = null;
    protected bool $active = false;
    protected int $quantity;

    public function getProductStreamId(): ?string
    {
        return $this->productStreamId;
    }

    public function setProductStreamId(?string $productStreamId): void
    {
        $this->productStreamId = $productStreamId;
    }

    public function getProductId(): ?string
    {
        return $this->productId;
    }

    public function setProductId(?string $productId): void
    {
        $this->productId = $productId;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): void
    {
        $this->quantity = $quantity;
    }

    public function getCartCombinationDiscountId(): string
    {
        return $this->CartCombinationDiscountId;
    }

    public function setCartCombinationDiscountId(string $CartCombinationDiscountId): void
    {
        $this->CartCombinationDiscountId = $CartCombinationDiscountId;
    }
}
