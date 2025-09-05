<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer\Collection;

use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\RestrictDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\SetNullOnDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ListField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToManyIdField;
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
            $ed = ExtractedDefinition::get(class: $referenceClass);

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
            $ed = ExtractedDefinition::get(class: $referenceClass);

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
                if (ExtractedDefinition::hasClass(Required::class, $fkFlags)) {
                    $fieldItems[] = (new ReferenceVersionField($referenceClass))
                        ->addFlags(new PrimaryKey(), new Required());
                } else {
                    $fieldItems[] = new ReferenceVersionField($referenceClass);
                }
            }

            $fieldItems[] = (new ManyToOneAssociationField(
                $ed->getPropertyName(),
                $ed->getFkStorageName(),
                $referenceClass
            ))->addFlags(new ApiAware(), ...$assocFlags);
        }

        return $fieldItems;
    }

    public static function getOneToManyFieldItems(string $localClass, array $references): array
    {
        $localEd = ExtractedDefinition::get(class: $localClass);

        $fieldItems = [];

        foreach ($references as $reference) {
            if (is_array($reference)) {
                $referenceClass = $reference[0];
                $assocFlags = $reference[1] ?? [new CascadeDelete()];
            } else {
                $referenceClass = $reference;
                $assocFlags = [new CascadeDelete()];
            }

            $referenceEd = ExtractedDefinition::get(
                class: $referenceClass
            );

            $fieldItems[] = (new OneToManyAssociationField(
                $referenceEd->getCollectionName(),
                $referenceClass,
                $localEd->getFkStorageName()
            ))->addFlags(new ApiAware(), ...$assocFlags);
        }

        return $fieldItems;
    }

    public static function getManyToManyFieldItems(string $localClass, array $references): array
    {
        $localEd = ExtractedDefinition::get(
            class: $localClass
        );

        $fieldItems = [];

        foreach ($references as $reference) {
            $referenceClass = $reference[0];
            $mappingDefinition = $reference[1];
            $flags = $reference[2] ?? [];
            $addReferenceIdsField = $reference[3] ?? false;

            $referenceEd = ExtractedDefinition::get(class: $referenceClass);
            // $mappingEd = ExtractedDefinition::get(class: $mappingDefinition);
            // TEST: $mappingEd->getCollectionName() mit $referenceEd->getCollectionName() getauscht

            $fieldItems[] = (new ManyToManyAssociationField(
                $referenceEd->getCollectionName(),
                $referenceClass,
                $mappingDefinition,
                $localEd->getFkStorageName(),
                $referenceEd->getFkStorageName())
            )->addFlags(new ApiAware(), new CascadeDelete(), ...$flags);

            if ($addReferenceIdsField) {
                $fieldItems[] = (new ManyToManyIdField(
                    $referenceEd->getFkStorageName() . 's',
                    $referenceEd->getFkPropertyName() . 'Ids',
                    $referenceEd->getCollectionName()
                ));
            }
        }

        return $fieldItems;
    }
}
