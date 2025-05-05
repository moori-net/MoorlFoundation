<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\EmbeddedMedia;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                add(EmbeddedMediaProductEntity $entity)
 * @method void                set(string $key, EmbeddedMediaProductEntity $entity)
 * @method EmbeddedMediaProductEntity[]    getIterator()
 * @method EmbeddedMediaProductEntity[]    getElements()
 * @method EmbeddedMediaProductEntity|null get(string $key)
 * @method EmbeddedMediaProductEntity|null first()
 * @method EmbeddedMediaProductEntity|null last()
 */
class EmbeddedMediaProductCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'moorl_media_product_collection';
    }

    protected function getExpectedClass(): string
    {
        return EmbeddedMediaProductEntity::class;
    }
}
