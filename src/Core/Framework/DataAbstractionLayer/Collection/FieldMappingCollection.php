<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer\Collection;

use Shopware\Core\Checkout\Order\OrderDefinition;
use Shopware\Core\Content\Category\CategoryDefinition;
use Shopware\Core\Content\Cms\Aggregate\CmsBlock\CmsBlockDefinition;
use Shopware\Core\Content\Cms\Aggregate\CmsSlot\CmsSlotDefinition;
use Shopware\Core\Content\Product\ProductDefinition;
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
    public static function getFieldItems(array $mappingClasses, array $versionClasses = []): array
    {
        $fieldItems = [];

        $versionClasses = array_unique(array_merge($versionClasses, [
            CategoryDefinition::class,
            ProductDefinition::class,
            OrderDefinition::class,
            CmsBlockDefinition::class,
            CmsSlotDefinition::class
        ]));

        foreach ($mappingClasses as $mappingClass) {
            $extracted = new ExtractedDefinition(
                class: $mappingClass,
                propertyName: true,
                fkStorageName: true,
                fkPropertyName: true
            );

            $fieldItems[] = (new FkField($extracted->getFkStorageName(), $extracted->getFkPropertyName(), $mappingClass))
                ->addFlags(new ApiAware(), new PrimaryKey(), new Required());

            $fieldItems[] = (new ManyToOneAssociationField($extracted->getPropertyName(), $extracted->getFkStorageName(), $mappingClass))
                ->addFlags(new CascadeDelete());

            if (in_array($mappingClass, $versionClasses)) {
                $fieldItems[] = (new ReferenceVersionField($mappingClass))
                    ->addFlags(new PrimaryKey(), new Required());
            }
        }

        return $fieldItems;
    }
}
