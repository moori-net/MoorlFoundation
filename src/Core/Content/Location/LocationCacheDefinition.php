<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\Location;

use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FloatField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class LocationCacheDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'moorl_location_cache';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return LocationCacheCollection::class;
    }

    public function getEntityClass(): string
    {
        return LocationCacheEntity::class;
    }

    public function getDefaults(): array
    {
        return [];
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new FkField('location_id', 'locationId', LocationDefinition::class))->addFlags(new PrimaryKey(), new Required()),
            (new IdField('entity_id', 'entityId'))->addFlags(new PrimaryKey(), new Required()),
            (new FloatField('distance', 'distance'))->addFlags(new Required()),
            new ManyToOneAssociationField('location', 'location_id', LocationDefinition::class),
        ]);
    }
}
