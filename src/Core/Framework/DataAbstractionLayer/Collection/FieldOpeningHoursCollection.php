<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer\Collection;

use MoorlFoundation\Core\Content\OpeningHours\OpeningHoursDefaults;
use MoorlFoundation\Core\Framework\DataAbstractionLayer\Field\Flags\EditField;
use MoorlFoundation\Core\Framework\DataAbstractionLayer\Field\Flags\VueComponent;
use Shopware\Core\Framework\DataAbstractionLayer\Field\JsonField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class FieldOpeningHoursCollection extends FieldCollection
{
    public static function getFieldItems(): array
    {
        return [
            (new StringField('time_zone', 'timeZone'))->addFlags(new EditField('text')),
            (new JsonField('opening_hours','openingHours'))->addFlags(new VueComponent('moorl-opening-hours')),
        ];
    }

    public static function getDefaults(): array
    {
        return [
            'timeZone' => 'Europe/Berlin',
            'openingHours' => OpeningHoursDefaults::getOpeningHours(),
        ];
    }
}
