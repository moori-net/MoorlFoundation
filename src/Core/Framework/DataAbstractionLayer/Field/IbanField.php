<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer\Field;

use MoorlFoundation\Core\Framework\DataAbstractionLayer\FieldSerializer\IbanFieldSerializer;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StorageAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;

class IbanField extends StringField implements StorageAware
{
    protected function getSerializerClass(): string
    {
        return IbanFieldSerializer::class;
    }
}
