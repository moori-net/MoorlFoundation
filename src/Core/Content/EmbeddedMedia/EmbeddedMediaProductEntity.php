<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\EmbeddedMedia;

use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class EmbeddedMediaProductEntity extends Entity
{
    use EntityIdTrait;

    protected int $priority = 0;
    protected bool $badge = false;
    protected bool $tab = false;
    protected bool $gallery = false;
    protected ?EmbeddedMediaEntity $embeddedMedia = null;
    protected ?ProductEntity $product = null;

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): void
    {
        $this->priority = $priority;
    }

    public function isBadge(): bool
    {
        return $this->badge;
    }

    public function setBadge(bool $badge): void
    {
        $this->badge = $badge;
    }

    public function isTab(): bool
    {
        return $this->tab;
    }

    public function setTab(bool $tab): void
    {
        $this->tab = $tab;
    }

    public function isGallery(): bool
    {
        return $this->gallery;
    }

    public function setGallery(bool $gallery): void
    {
        $this->gallery = $gallery;
    }

    public function getEmbeddedMedia(): ?EmbeddedMediaEntity
    {
        return $this->embeddedMedia;
    }

    public function setEmbeddedMedia(?EmbeddedMediaEntity $embeddedMedia): void
    {
        $this->embeddedMedia = $embeddedMedia;
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
