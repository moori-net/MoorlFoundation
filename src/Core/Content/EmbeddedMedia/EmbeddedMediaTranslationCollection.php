<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\EmbeddedMedia;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

class EmbeddedMediaTranslationCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'moorl_media_translation_collection';
    }

    protected function getExpectedClass(): string
    {
        return EmbeddedMediaTranslationEntity::class;
    }
}
