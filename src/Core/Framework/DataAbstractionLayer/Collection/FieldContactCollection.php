<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer\Collection;

use MoorlFoundation\Core\Framework\DataAbstractionLayer\Field\Flags\EditField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class FieldContactCollection extends FieldCollection
{
    public static function getFieldItems(bool $flag = true): array
    {
        if (!$flag) return [];

        return [
            (new StringField('email', 'email'))->addFlags(new EditField(EditField::TEXT)),
            (new StringField('phone_number', 'phoneNumber'))->addFlags(new EditField(EditField::TEXT)),
            (new StringField('shop_url', 'shopUrl'))->addFlags(new EditField('text')),
            (new StringField('merchant_url', 'merchantUrl'))->addFlags(new EditField('text')),
        ];
    }
}
