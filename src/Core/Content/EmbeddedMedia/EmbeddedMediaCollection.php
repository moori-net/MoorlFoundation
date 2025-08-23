<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\EmbeddedMedia;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                add(EmbeddedMediaEntity $entity)
 * @method void                set(string $key, EmbeddedMediaEntity $entity)
 * @method EmbeddedMediaEntity[]    getIterator()
 * @method EmbeddedMediaEntity[]    getElements()
 * @method EmbeddedMediaEntity|null get(string $key)
 * @method EmbeddedMediaEntity|null first()
 * @method EmbeddedMediaEntity|null last()
 */
class EmbeddedMediaCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'moorl_media_collection';
    }

    protected function getExpectedClass(): string
    {
        return EmbeddedMediaEntity::class;
    }
}
