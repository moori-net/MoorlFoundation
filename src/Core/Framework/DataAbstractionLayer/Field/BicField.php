<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer\Field;

use MoorlFoundation\Core\Framework\DataAbstractionLayer\FieldSerializer\BicFieldSerializer;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StorageAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;

class BicField extends StringField implements StorageAware
{
    protected function getSerializerClass(): string
    {
        return BicFieldSerializer::class;
    }
}
