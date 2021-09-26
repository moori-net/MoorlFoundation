<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\Location;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                add(LocationEntity $entity)
 * @method void                set(string $key, LocationEntity $entity)
 * @method LocationEntity[]    getIterator()
 * @method LocationEntity[]    getElements()
 * @method LocationEntity|null get(string $key)
 * @method LocationEntity|null first()
 * @method LocationEntity|null last()
 */
class LocationCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'moorl_locations_collection';
    }

    protected function getExpectedClass(): string
    {
        return LocationEntity::class;
    }
}
