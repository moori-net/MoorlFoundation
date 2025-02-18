<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\ImageMap;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                       add(ImageMapItemEntity $entity)
 * @method void                       set(string $key, ImageMapItemEntity $entity)
 * @method ImageMapItemEntity[]    getIterator()
 * @method ImageMapItemEntity[]    getElements()
 * @method ImageMapItemEntity|null get(string $key)
 * @method ImageMapItemEntity|null first()
 * @method ImageMapItemEntity|null last()
 */
class ImageMapItemCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return ImageMapItemEntity::class;
    }
}
