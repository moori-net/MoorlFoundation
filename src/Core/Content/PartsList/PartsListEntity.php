<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\PartsList;

use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class PartsListEntity extends Entity
{
    use EntityIdTrait;

    protected ?string $productStreamId = null;
    protected ?string $productId = null;
    protected array $groups = [];
    protected array $options = [];
    protected array $productStreams = [];
    protected int $quantity = 0;
    protected int $temporaryQuantity = 0;
    protected int $calcX = 0;
    protected int $calcY = 0;
    protected int $calcZ = 0;
    protected ?ProductEntity $product = null;

    public static function createFromProduct(ProductEntity $product): self
    {
        $self = new self();

        $self->setId($product->getId());
        $self->productId = $product->getId();
        $self->product = $product;
        $self->quantity = 0;

        return $self;
    }

    public function getGroups(): array
    {
        return $this->groups;
    }

    public function setGroups(array $groups): void
    {
        $this->groups = $groups;
    }

    public function addGroup(?string $group): void
    {
        if (!$group) {
            return;
        }

        $this->groups[] = $group;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function setOptions(array $options): void
    {
        $this->options = $options;
    }

    public function addOption(?string $option): void
    {
        if (!$option) {
            return;
        }

        $this->options[] = $option;
    }

    public function getProductStreams(): array
    {
        return $this->productStreams;
    }

    public function setProductStreams(array $productStreams): void
    {
        $this->productStreams = $productStreams;
    }

    public function addProductStream(?string $productStream): void
    {
        if (!$productStream) {
            return;
        }

        $this->productStreams[] = $productStream;
    }

    public function getTemporaryQuantity(): int
    {
        return $this->temporaryQuantity;
    }

    public function setTemporaryQuantity(int $temporaryQuantity): void
    {
        $this->temporaryQuantity = $temporaryQuantity;
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

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): void
    {
        $this->quantity = $quantity;
    }

    public function getCalcX(): int
    {
        return $this->calcX;
    }

    public function setCalcX(int $calcX): void
    {
        $this->calcX = $calcX;
    }

    public function getCalcY(): int
    {
        return $this->calcY;
    }

    public function setCalcY(int $calcY): void
    {
        $this->calcY = $calcY;
    }

    public function getCalcZ(): int
    {
        return $this->calcZ;
    }

    public function setCalcZ(int $calcZ): void
    {
        $this->calcZ = $calcZ;
    }

    public function getProduct(): ?ProductEntity
    {
        return $this->product;
    }

    public function setProduct(?ProductEntity $product): void
    {
        $this->product = $product;
    }
}
