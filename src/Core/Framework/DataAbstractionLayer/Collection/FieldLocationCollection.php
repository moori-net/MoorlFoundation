<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer\Collection;

use MoorlFoundation\Core\Content\Location\LocationCacheDefinition;
use MoorlFoundation\Core\Content\Marker\MarkerDefinition;
use MoorlFoundation\Core\Framework\DataAbstractionLayer\Field\Flags\EditField;
use MoorlFoundation\Core\Framework\DataAbstractionLayer\Field\Flags\LabelProperty;
use MoorlFoundation\Core\Framework\DataAbstractionLayer\FieldCollectionMergeTrait;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FloatField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class FieldLocationCollection extends FieldCollection
{
    use FieldCollectionMergeTrait;

    public function __construct()
    {
        return new parent([
            (new FloatField('location_lat','locationLat'))->addFlags(new EditField('number')),
            (new FloatField('location_lon','locationLon'))->addFlags(new EditField('number')),
            (new BoolField('auto_location', 'autoLocation'))->addFlags(new EditField('switch')),
            new FkField('moorl_marker_id', 'markerId', MarkerDefinition::class),
            (new ManyToOneAssociationField('marker', 'moorl_marker_id', MarkerDefinition::class, 'id', true))->addFlags(new EditField(), new LabelProperty('name')),
            new OneToManyAssociationField('locationCache', LocationCacheDefinition::class, 'entity_id')
        ]);
    }
}
