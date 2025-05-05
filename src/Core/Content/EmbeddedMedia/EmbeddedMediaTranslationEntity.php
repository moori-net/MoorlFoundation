<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\EmbeddedMedia;

use Shopware\Core\Content\Media\MediaEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;

class EmbeddedMediaTranslationEntity extends Entity
{
    protected ?string $name = null;
    protected ?string $description = null;
    protected ?string $embeddedUrl = null;
    protected ?string $embeddedId = null;
    protected ?MediaEntity $cover = null;
    protected ?MediaEntity $media = null;
}
