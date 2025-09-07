<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer\FieldSerializer;

use Shopware\Core\Framework\DataAbstractionLayer\Field\Field;
use Shopware\Core\Framework\DataAbstractionLayer\FieldSerializer\StringFieldSerializer;
use Symfony\Component\Validator\Constraints\Iban;

class IbanFieldSerializer extends StringFieldSerializer
{
    protected function getConstraints(Field $field): array
    {
        return array_merge(parent::getConstraints($field), [new Iban()]);
    }
}
