<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer\Collection;

use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class FieldMultiEntityCollection extends FieldCollection
{
    public static function getFieldItems(array $referenceClasses, array $fkFlags = [], array $assocFlags = []): array
    {
        $fieldItems = [];

        foreach ($referenceClasses as $referenceClass) {
            $ed = ExtractedDefinition::get(
                class: $referenceClass
            );

            $fieldItems[] = (new FkField(
                $ed->getFkStorageName(),
                $ed->getFkPropertyName(),
                $referenceClass
            ))->addFlags(new ApiAware(), ...$fkFlags);

            $fieldItems[] = (new ManyToOneAssociationField(
                $ed->getPropertyName(),
                $ed->getFkStorageName(),
                $referenceClass
            ))->addFlags(...$assocFlags);
        }

        return $fieldItems;
    }

    public static function getManyToOneFieldItems(array $referenceClasses, array $fkFlags = [], array $assocFlags = []): array
    {
        return self::getFieldItems($referenceClasses, $fkFlags, $assocFlags);
    }

    public static function getOneToManyFieldItems(string $parentClass, array $referenceClasses): array
    {
        $parentEd = ExtractedDefinition::get(class: $parentClass);

        $fieldItems = [];

        foreach ($referenceClasses as $referenceClass) {
            $ed = ExtractedDefinition::get(
                class: $referenceClass
            );

            $fieldItems[] = (new OneToManyAssociationField(
                $ed->getCollectionName(),
                $referenceClass,
                $parentEd->getReferenceField()
            ))->addFlags(new ApiAware(), new CascadeDelete());
        }

        return $fieldItems;
    }
}
