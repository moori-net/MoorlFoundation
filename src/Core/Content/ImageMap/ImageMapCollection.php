<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\ImageMap;

use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                       add(ImageMapEntity $entity)
 * @method void                       set(string $key, ImageMapEntity $entity)
 * @method ImageMapEntity[]    getIterator()
 * @method ImageMapEntity[]    getElements()
 * @method ImageMapEntity|null get(string $key)
 * @method ImageMapEntity|null first()
 * @method ImageMapEntity|null last()
 */
class ImageMapCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return ImageMapEntity::class;
    }
}
