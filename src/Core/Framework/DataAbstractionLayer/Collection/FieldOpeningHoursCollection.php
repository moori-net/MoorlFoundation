<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer\Collection;

use Appflix\DewaShop\Core\Framework\DataAbstractionLayer\Field\Flags\EditField;
use MoorlFoundation\Core\Framework\DataAbstractionLayer\Field\Flags\VueComponent;
use MoorlFoundation\Core\Framework\DataAbstractionLayer\FieldCollectionMergeTrait;
use Shopware\Core\Framework\DataAbstractionLayer\Field\JsonField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class FieldOpeningHoursCollection extends FieldCollection
{
    use FieldCollectionMergeTrait;

    public function __construct()
    {
        return new parent([
            (new StringField('time_zone', 'timeZone'))->addFlags(new EditField('text')),
            (new JsonField('opening_hours','openingHours'))->addFlags(new VueComponent('moorl-opening-hours')),
        ]);
    }
}
