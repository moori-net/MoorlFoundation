<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\EmbeddedMedia;

use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ReferenceVersionField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class EmbeddedMediaProductDefinition extends EntityDefinition
{
    final public const ENTITY_NAME = 'moorl_media_product';
    final public const PROPERTY_NAME = 'embeddedMediaProduct';
    final public const COLLECTION_NAME = 'embeddedMediaProductCollection';
    final public const EXTENSION_COLLECTION_NAME = 'moorlEmbeddedMediaProductCollection';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return EmbeddedMediaProductEntity::class;
    }

    public function getCollectionClass(): string
    {
        return EmbeddedMediaProductCollection::class;
    }

    public function getDefaults(): array
    {
        return [];
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),
            (new FkField('product_id', 'productId', ProductDefinition::class))->addFlags(new Required()),
            (new ReferenceVersionField(ProductDefinition::class))->addFlags(new Required()),
            (new FkField('moorl_media_id', 'embeddedMediaId', EmbeddedMediaDefinition::class))->addFlags(new Required()),

            (new IntField('priority', 'priority'))->addFlags(),
            (new BoolField('badge', 'badge'))->addFlags(),
            (new BoolField('gallery', 'gallery'))->addFlags(),
            (new BoolField('tab', 'tab'))->addFlags(),

            (new ManyToOneAssociationField(
                'product',
                'product_id',
                ProductDefinition::class
            ))->addFlags(),

            (new ManyToOneAssociationField(
                EmbeddedMediaDefinition::PROPERTY_NAME,
                'moorl_media_id',
                EmbeddedMediaDefinition::class
            ))->addFlags(),
        ]);
    }
}
