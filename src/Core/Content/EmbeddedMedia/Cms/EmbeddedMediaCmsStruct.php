<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\EmbeddedMedia\Cms;

use MoorlFoundation\Core\Content\EmbeddedMedia\EmbeddedMediaEntity;
use Shopware\Core\Framework\Struct\Struct;

class EmbeddedMediaCmsStruct extends Struct
{
    protected ?EmbeddedMediaEntity $embeddedMedia = null;

    public function getEmbeddedMedia(): ?EmbeddedMediaEntity
    {
        return $this->embeddedMedia;
    }

    public function setEmbeddedMedia(?EmbeddedMediaEntity $embeddedMedia): void
    {
        $this->embeddedMedia = $embeddedMedia;
    }
}
