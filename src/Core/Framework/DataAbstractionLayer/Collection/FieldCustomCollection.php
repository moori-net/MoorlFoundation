<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer\Collection;

use MoorlFoundation\Core\Framework\DataAbstractionLayer\Field\Flags\EditField;
use MoorlFoundation\Core\Framework\DataAbstractionLayer\FieldCollectionMergeTrait;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class FieldCustomCollection extends FieldCollection
{
    use FieldCollectionMergeTrait;

    public function __construct()
    {
        return new parent(self::getFieldItems());
    }

    public static function getFieldItems(): array
    {
        return [
            (new StringField('custom1', 'custom1'))->addFlags(new EditField('text')),
            (new StringField('custom2', 'custom2'))->addFlags(new EditField('text')),
            (new StringField('custom3', 'custom3'))->addFlags(new EditField('text')),
            (new StringField('custom4', 'custom4'))->addFlags(new EditField('text')),
        ];
    }
}
