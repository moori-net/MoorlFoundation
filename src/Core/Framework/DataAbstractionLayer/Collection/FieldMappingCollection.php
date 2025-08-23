<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer\Collection;

use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ReferenceVersionField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class FieldMappingCollection extends FieldCollection
{
    public static function getFieldItems(array $mappingClasses): array
    {
        $fieldItems = [];

        foreach ($mappingClasses as $mappingClass) {
            $mappingEd = ExtractedDefinition::get(
                class: $mappingClass
            );

            $fieldItems[] = (new FkField($mappingEd->getFkStorageName(), $mappingEd->getFkPropertyName(), $mappingClass))
                ->addFlags(new ApiAware(), new PrimaryKey(), new Required());

            $fieldItems[] = (new ManyToOneAssociationField($mappingEd->getPropertyName(), $mappingEd->getFkStorageName(), $mappingClass))
                ->addFlags(new CascadeDelete());

            if (ExtractedDefinition::isVersionDefinition($mappingClass)) {
                $fieldItems[] = (new ReferenceVersionField($mappingClass))
                    ->addFlags(new PrimaryKey(), new Required());
            }
        }

        return $fieldItems;
    }
}
