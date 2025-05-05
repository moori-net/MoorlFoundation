<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\Product;

use MoorlFoundation\Core\Content\EmbeddedMedia\EmbeddedMediaProductDefinition;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Inherited;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class ProductExtension extends EntityExtension
{
    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            (new OneToManyAssociationField(
                EmbeddedMediaProductDefinition::EXTENSION_COLLECTION_NAME,
                EmbeddedMediaProductDefinition::class,
                'product_id'
            ))->addFlags(new Inherited())
        );
    }

    public function getEntityName(): string
    {
        return ProductDefinition::ENTITY_NAME;
    }
}
