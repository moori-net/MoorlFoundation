<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\Location;

use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FloatField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\JsonField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class LocationDefinition extends EntityDefinition
{
    final public const ENTITY_NAME = 'moorl_location';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return LocationCollection::class;
    }

    public function getEntityClass(): string
    {
        return LocationEntity::class;
    }

    public function getDefaults(): array
    {
        return [];
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),
            (new JsonField('payload', 'payload'))->addFlags(new Required()),
            (new FloatField('location_lat','locationLat')),
            (new FloatField('location_lon','locationLon')),
            new OneToManyAssociationField('locationCache', LocationCacheDefinition::class, 'location_id')
        ]);
    }
}
