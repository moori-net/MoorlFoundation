<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\CmsElementConfig;

use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                       add(CmsElementConfigEntity $entity)
 * @method void                       set(string $key, CmsElementConfigEntity $entity)
 * @method CmsElementConfigEntity[]    getIterator()
 * @method CmsElementConfigEntity[]    getElements()
 * @method CmsElementConfigEntity|null get(string $key)
 * @method CmsElementConfigEntity|null first()
 * @method CmsElementConfigEntity|null last()
 */
class CmsElementConfigCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return CmsElementConfigEntity::class;
    }
}
