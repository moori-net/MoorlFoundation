<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\ImageMap;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void            add(ImageMapTranslationEntity $entity)
 * @method void            set(string $key, ImageMapTranslationEntity $entity)
 * @method ImageMapTranslationEntity[]    getIterator()
 * @method ImageMapTranslationEntity[]    getElements()
 * @method ImageMapTranslationEntity|null get(string $key)
 * @method ImageMapTranslationEntity|null first()
 * @method ImageMapTranslationEntity|null last()
 */
class ImageMapTranslationCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return ImageMapTranslationEntity::class;
    }
}
