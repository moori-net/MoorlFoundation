<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer\Collection;

use MoorlFoundation\Core\Content\Location\LocationCacheDefinition;
use MoorlFoundation\Core\Content\Marker\MarkerDefinition;
use MoorlFoundation\Core\Framework\DataAbstractionLayer\Field\DoubleField;
use MoorlFoundation\Core\Framework\DataAbstractionLayer\Field\Flags\EditField;
use MoorlFoundation\Core\Framework\DataAbstractionLayer\Field\Flags\LabelProperty;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\SetNullOnDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class FieldLocationCollection extends FieldCollection
{
    public static function getFieldItems(bool $flag = true): array
    {
        if (!$flag) return [];

        return [
            (new DoubleField('location_lat','locationLat'))->addFlags(new EditField(EditField::NUMBER)),
            (new DoubleField('location_lon','locationLon'))->addFlags(new EditField(EditField::NUMBER)),
            (new BoolField('auto_location', 'autoLocation'))->addFlags(new EditField(EditField::SWITCH)),
            new FkField('moorl_marker_id', 'markerId', MarkerDefinition::class),
            (new ManyToOneAssociationField('marker', 'moorl_marker_id', MarkerDefinition::class, 'id', true))->addFlags(new SetNullOnDelete(), new EditField(), new LabelProperty('name')),
            new OneToManyAssociationField('locationCache', LocationCacheDefinition::class, 'entity_id')
        ];
    }

    public static function getDefaults(): array
    {
        return [
            'locationLat' => 0,
            'locationLon' => 0,
            'autoLocation' => false
        ];
    }
}
