<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\EmbeddedMedia;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCustomFieldsTrait;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class EmbeddedMediaEntity extends Entity
{
    use EntityIdTrait;
    use EntityCustomFieldsTrait;

    protected int $duration = 0;
    protected ?string $technicalName = null;
    protected ?string $productStreamId = null;
    protected ?string $embeddedType = null;

    protected array $config = [];
    protected bool $active = false;
    protected ?EmbeddedMediaEntity $configParent = null;

    public function getEmbeddedType(): ?string
    {
        return $this->embeddedType;
    }

    public function setEmbeddedType(?string $embeddedType): void
    {
        $this->embeddedType = $embeddedType;
    }

    public function getDuration(): int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): void
    {
        $this->duration = $duration;
    }

    public function getTechnicalName(): ?string
    {
        return $this->technicalName;
    }

    public function setTechnicalName(?string $technicalName): void
    {
        $this->technicalName = $technicalName;
    }

    public function getProductStreamId(): ?string
    {
        return $this->productStreamId;
    }

    public function setProductStreamId(?string $productStreamId): void
    {
        $this->productStreamId = $productStreamId;
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    public function setConfig(array $config): void
    {
        $this->config = $config;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    public function getConfigParent(): ?EmbeddedMediaEntity
    {
        return $this->configParent;
    }

    public function setConfigParent(?EmbeddedMediaEntity $configParent): void
    {
        $this->configParent = $configParent;
    }
}
