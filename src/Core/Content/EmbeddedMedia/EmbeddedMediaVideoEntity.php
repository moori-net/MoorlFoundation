<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\EmbeddedMedia;

use Shopware\Core\Content\Media\MediaEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class EmbeddedMediaVideoEntity extends Entity
{
    use EntityIdTrait;

    protected ?string $minWidth = null;
    protected ?MediaEntity $media = null;
    protected ?EmbeddedMediaEntity $embeddedMedia = null;

    public function getMinWidth(): ?string
    {
        if (is_numeric($this->minWidth)) {
            return sprintf("%spx", $this->minWidth);
        }

        return $this->minWidth;
    }

    public function setMinWidth(?string $minWidth): void
    {
        $this->minWidth = $minWidth;
    }

    public function getMedia(): ?MediaEntity
    {
        return $this->media;
    }

    public function setMedia(?MediaEntity $media): void
    {
        $this->media = $media;
    }

    public function getEmbeddedMedia(): ?EmbeddedMediaEntity
    {
        return $this->embeddedMedia;
    }

    public function setEmbeddedMedia(?EmbeddedMediaEntity $embeddedMedia): void
    {
        $this->embeddedMedia = $embeddedMedia;
    }
}
