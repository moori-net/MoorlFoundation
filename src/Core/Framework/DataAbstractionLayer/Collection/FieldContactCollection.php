<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer\Collection;

use MoorlFoundation\Core\Framework\DataAbstractionLayer\Field\Flags\EditField;
use MoorlFoundation\Core\Framework\DataAbstractionLayer\FieldCollectionMergeTrait;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class FieldContactCollection extends FieldCollection
{
    use FieldCollectionMergeTrait;

    public function __construct()
    {
        return new parent([
            (new StringField('email', 'email'))->addFlags(new EditField('text')),
            (new StringField('phone_number', 'phoneNumber'))->addFlags(new EditField('text')),
            (new StringField('shop_url', 'shopUrl'))->addFlags(new EditField('text')),
            (new StringField('merchant_url', 'merchantUrl'))->addFlags(new EditField('text')),
        ]);
    }
}
