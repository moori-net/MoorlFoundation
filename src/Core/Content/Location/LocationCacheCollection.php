<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\Location;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                add(LocationCacheEntity $entity)
 * @method void                set(string $key, LocationCacheEntity $entity)
 * @method LocationCacheEntity[]    getIterator()
 * @method LocationCacheEntity[]    getElements()
 * @method LocationCacheEntity|null get(string $key)
 * @method LocationCacheEntity|null first()
 * @method LocationCacheEntity|null last()
 */
class LocationCacheCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'moorl_location_cache_collection';
    }

    protected function getExpectedClass(): string
    {
        return LocationCacheEntity::class;
    }
}
