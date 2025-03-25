<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\PartsList;

use MoorlFoundation\Core\Framework\DataAbstractionLayer\EntitySvgShapeTrait;
use Shopware\Core\Content\Category\CategoryEntity;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class PartsListEntity extends Entity
{
    use EntityIdTrait;
    use EntitySvgShapeTrait;

    protected ?string $productStreamId = null;
    protected ?string $productId = null;
    protected ?string $categoryId = null;
    protected bool $active = false;
    protected int $quantity = 1;
    protected int $priority = 0;
    protected int $posLeft = 50;
    protected int $posTop = 50;
    protected ?ProductEntity $product = null;
    protected ?CategoryEntity $category = null;

    public static function createFromProduct(ProductEntity $product): self
    {
        $self = new self();

        $self->setId($product->getId());
        $self->productId = $product->getId();
        $self->product = $product;
        $self->quantity = 0;

        return $self;
    }

    public function getCategoryId(): ?string
    {
        return $this->categoryId;
    }

    public function setCategoryId(?string $categoryId): void
    {
        $this->categoryId = $categoryId;
    }

    public function getCategory(): ?CategoryEntity
    {
        return $this->category;
    }

    public function setCategory(?CategoryEntity $category): void
    {
        $this->category = $category;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): void
    {
        $this->priority = $priority;
    }

    public function getProduct(): ?ProductEntity
    {
        return $this->product;
    }

    public function setProduct(?ProductEntity $product): void
    {
        $this->product = $product;
    }

    public function getPosLeft(): int
    {
        return $this->posLeft;
    }

    public function setPosLeft(int $posLeft): void
    {
        $this->posLeft = $posLeft;
    }

    public function getPosTop(): int
    {
        return $this->posTop;
    }

    public function setPosTop(int $posTop): void
    {
        $this->posTop = $posTop;
    }

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
}
