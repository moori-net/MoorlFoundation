<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer\FieldSerializer;

use MoorlFoundation\Core\Framework\DataAbstractionLayer\Field\DistanceField;
use Shopware\Core\Framework\DataAbstractionLayer\Exception\InvalidSerializerFieldException;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Field;
use Shopware\Core\Framework\DataAbstractionLayer\FieldSerializer\FieldSerializerInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Write\DataStack\KeyValuePair;
use Shopware\Core\Framework\DataAbstractionLayer\Write\EntityExistence;
use Shopware\Core\Framework\DataAbstractionLayer\Write\WriteParameterBag;

class DistanceFieldSerializer implements FieldSerializerInterface
{
    public function encode(
        Field $field,
        EntityExistence $existence,
        KeyValuePair $data,
        WriteParameterBag $parameters
    ): \Generator {
        if (!$field instanceof DistanceField) {
            throw new InvalidSerializerFieldException(DistanceField::class, $field);
        }

        yield from [];
    }

    public function decode(Field $field, $value): ?float
    {
        return $value === null ? null : (float) $value;
    }
}
