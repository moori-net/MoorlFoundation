<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer\Collection;

use MoorlFoundation\Core\Framework\DataAbstractionLayer\Field\Flags\EditField;
use MoorlFoundation\Core\Framework\DataAbstractionLayer\FieldCollectionMergeTrait;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FloatField;
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
        ]);
    }
}
