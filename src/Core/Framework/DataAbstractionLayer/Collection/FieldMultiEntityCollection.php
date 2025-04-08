<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer\Collection;

use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\RestrictDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\SetNullOnDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ReferenceVersionField;
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

    public static function getManyToOneFieldItems(array $references): array
    {
        $fieldItems = [];

        foreach ($references as [$referenceClass, $fkFlags, $assocFlags]) {
            $ed = ExtractedDefinition::get(
                class: $referenceClass
            );

            // Auto fix rules
            if (ExtractedDefinition::hasClass(Required::class, $fkFlags)) {
                if (!ExtractedDefinition::hasClass(RestrictDelete::class, $assocFlags)) {
                    $assocFlags[] = new CascadeDelete();
                }
            } else {
                if (!ExtractedDefinition::hasClass(SetNullOnDelete::class, $assocFlags)) {
                    $assocFlags[] = new SetNullOnDelete();
                }
            }

            $fieldItems[] = (new FkField(
                $ed->getFkStorageName(),
                $ed->getFkPropertyName(),
                $referenceClass
            ))->addFlags(new ApiAware(), ...$fkFlags);

            if (ExtractedDefinition::isVersionDefinition($referenceClass)) {
                $fieldItems[] = (new ReferenceVersionField($referenceClass))
                    ->addFlags(new Required());
            }

            $fieldItems[] = (new ManyToOneAssociationField(
                $ed->getPropertyName(),
                $ed->getFkStorageName(),
                $referenceClass
            ))->addFlags(new ApiAware(), ...$assocFlags);
        }

        return $fieldItems;
    }

    public static function getOneToManyFieldItems(string $localClass, array $referenceClasses): array
    {
        $localEd = ExtractedDefinition::get(class: $localClass);

        $fieldItems = [];

        foreach ($referenceClasses as $referenceClass) {
            $referenceEd = ExtractedDefinition::get(
                class: $referenceClass
            );

            $fieldItems[] = (new OneToManyAssociationField(
                $referenceEd->getCollectionName(),
                $referenceClass,
                $localEd->getFkStorageName()
            ))->addFlags(new ApiAware(), new CascadeDelete());
        }

        return $fieldItems;
    }

    public static function getManyToManyFieldItems(string $localClass, array $references): array
    {
        $localEd = ExtractedDefinition::get(
            class: $localClass
        );

        $fieldItems = [];

        foreach ($references as [$referenceClass, $mappingDefinition, $flags]) {
            $referenceEd = ExtractedDefinition::get(
                class: $referenceClass
            );
            $mappingEd = ExtractedDefinition::get(
                class: $mappingDefinition
            );

            $fieldItems[] = (new ManyToManyAssociationField(
                $mappingEd->getCollectionName(),
                $referenceClass,
                $mappingDefinition,
                $localEd->getFkStorageName(),
                $referenceEd->getFkStorageName())
            )->addFlags(new ApiAware(), new CascadeDelete(), ...$flags);
        }

        return $fieldItems;
    }
}
