<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\CartCombinationDiscount;

use MoorlFoundation\Core\Framework\DataAbstractionLayer\EntityTimeframeTrait;
use Shopware\Core\Content\Media\MediaEntity;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCustomFieldsTrait;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Shopware\Core\Framework\DataAbstractionLayer\Pricing\PriceCollection;

class CartCombinationDiscountEntity extends Entity
{
    use EntityIdTrait;
    use EntityTimeframeTrait;
    use EntityCustomFieldsTrait;

    protected ?string $mediaId = null;
    protected ?string $productId = null;
    protected bool $active = false;
    protected bool $hidden = false;
    protected string $countdownType;
    protected string $discountType;
    protected float $discountValue;
    protected ?PriceCollection $discountPrice = null;
    protected int $maxStacks = 1;
    protected int $priority = 0;
    protected string $name;
    protected ?string $description = null;
    protected ?MediaEntity $media = null;
    protected ?ProductEntity $product = null;
    protected ?CartCombinationDiscountItemCollection $items = null;

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): void
    {
        $this->priority = $priority;
    }

    public function getMediaId(): ?string
    {
        return $this->mediaId;
    }

    public function setMediaId(?string $mediaId): void
    {
        $this->mediaId = $mediaId;
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

    public function isHidden(): bool
    {
        return $this->hidden;
    }

    public function setHidden(bool $hidden): void
    {
        $this->hidden = $hidden;
    }

    public function getCountdownType(): string
    {
        return $this->countdownType;
    }

    public function setCountdownType(string $countdownType): void
    {
        $this->countdownType = $countdownType;
    }

    public function getDiscountType(): string
    {
        return $this->discountType;
    }

    public function setDiscountType(string $discountType): void
    {
        $this->discountType = $discountType;
    }

    public function getDiscountValue(): float
    {
        return $this->discountValue;
    }

    public function setDiscountValue(float $discountValue): void
    {
        $this->discountValue = $discountValue;
    }

    public function getDiscountPrice(): ?PriceCollection
    {
        return $this->discountPrice;
    }

    public function setDiscountPrice(?PriceCollection $discountPrice): void
    {
        $this->discountPrice = $discountPrice;
    }

    public function getMaxStacks(): int
    {
        return $this->maxStacks;
    }

    public function setMaxStacks(int $maxStacks): void
    {
        $this->maxStacks = $maxStacks;
    }

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

    public function getMedia(): ?MediaEntity
    {
        return $this->media;
    }

    public function setMedia(?MediaEntity $media): void
    {
        $this->media = $media;
    }

    public function getProduct(): ?ProductEntity
    {
        return $this->product;
    }

    public function setProduct(?ProductEntity $product): void
    {
        $this->product = $product;
    }

    public function getItems(): ?CartCombinationDiscountItemCollection
    {
        return $this->items;
    }

    public function setItems(?CartCombinationDiscountItemCollection $items): void
    {
        $this->items = $items;
    }
}
