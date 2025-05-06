<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\EmbeddedMedia;

use Shopware\Core\Content\Media\MediaEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCustomFieldsTrait;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class EmbeddedMediaEntity extends Entity
{
    use EntityIdTrait;
    use EntityCustomFieldsTrait;

    protected int $duration = 0;
    protected ?string $technicalName = null;
    protected ?string $type = null;

    protected array $config = [];
    protected bool $active = false;
    protected ?string $embeddedUrl = null;
    protected ?string $embeddedId = null;
    protected ?MediaEntity $cover = null;
    protected ?MediaEntity $media = null;
    protected ?EmbeddedMediaEntity $configParent = null;

    public function getEmbeddedUrl(): ?string
    {
        return $this->embeddedUrl;
    }

    public function setEmbeddedUrl(?string $embeddedUrl): void
    {
        $this->embeddedUrl = $embeddedUrl;
    }

    public function getMedia(): ?MediaEntity
    {
        return $this->media;
    }

    public function setMedia(?MediaEntity $media): void
    {
        $this->media = $media;
    }

    public function getCover(): ?MediaEntity
    {
        return $this->cover;
    }

    public function setCover(?MediaEntity $cover): void
    {
        $this->cover = $cover;
    }

    public function getEmbeddedId(): ?string
    {
        return $this->embeddedId;
    }

    public function setEmbeddedId(?string $embeddedId): void
    {
        $this->embeddedId = $embeddedId;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): void
    {
        $this->type = $type;
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
