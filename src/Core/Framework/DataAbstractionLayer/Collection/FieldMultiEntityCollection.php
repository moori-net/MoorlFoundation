<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer\Collection;

use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;

class FieldMultiEntityCollection extends FieldCollection
{
    public static function getFieldItems(array $referenceClasses, array $fkFlags = [], array $assocFlags = []): array
    {
        $fieldItems = [];

        foreach ($referenceClasses as $referenceClass) {
            if (!defined("{$referenceClass}::ENTITY_NAME")) {
                continue;
            }

            $entityName = $propertyName = $referenceClass::ENTITY_NAME;
            $storageName = $entityName . '_id';

            if (defined("{$referenceClass}::PROPERTY_NAME")) {
                $propertyName = $referenceClass::PROPERTY_NAME;
            }

            $fieldItems[] = (new FkField(
                $storageName,
                self::kebabCaseToCamelCase($propertyName . '_id'),
                $referenceClass
            ))->addFlags(new ApiAware(), ...$fkFlags);

            $fieldItems[] = (new ManyToOneAssociationField(
                self::kebabCaseToCamelCase($propertyName),
                $storageName,
                $referenceClass,
                'id',
                false
            ))->addFlags(...$assocFlags);
        }

        return $fieldItems;
    }

    protected static function kebabCaseToCamelCase(string $string): string
    {
        return (new CamelCaseToSnakeCaseNameConverter())->denormalize(str_replace('-', '_', $string));
    }
}
