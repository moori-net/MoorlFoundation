<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\ImageMap;

use MoorlFoundation\Core\Framework\DataAbstractionLayer\Field\Flags\EditField;
use MoorlFoundation\Core\Framework\DataAbstractionLayer\Field\Flags\LabelProperty;
use MoorlFoundation\Core\Framework\DataAbstractionLayer\Field\Flags\VueComponent;
use Shopware\Core\Content\Category\CategoryDefinition;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Content\ProductStream\ProductStreamDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\JsonField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ReferenceVersionField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class ImageMapItemDefinition extends EntityDefinition
{
    final public const ENTITY_NAME = 'moorl_image_map_item';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return ImageMapItemCollection::class;
    }

    public function getEntityClass(): string
    {
        return ImageMapItemEntity::class;
    }

    public function getDefaults(): array
    {
        return [
            'active' => true,
            'quantity' => 1,
            'priority' => 0,
            'posLeft' => 50,
            'posTop' => 50,
        ];
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),
            (new FkField('moorl_image_map_id', 'combinationDiscountId', ImageMapDefinition::class))->addFlags(new Required()),
            new FkField('product_stream_id', 'productStreamId', ProductStreamDefinition::class),
            new FkField('product_id', 'productId', ProductDefinition::class),
            (new ReferenceVersionField(ProductDefinition::class))->addFlags(new Required()),
            new FkField('category_id', 'categoryId', CategoryDefinition::class),
            (new ReferenceVersionField(CategoryDefinition::class))->addFlags(new Required()),
            (new BoolField('active', 'active'))->addFlags(new EditField(EditField::SWITCH)),
            (new IntField('quantity', 'quantity'))->addFlags(new Required(), new EditField(EditField::NUMBER)),
            (new IntField('priority', 'priority'))->addFlags(new Required(), new EditField(EditField::NUMBER)),
            (new IntField('pos_left', 'posLeft'))->addFlags(new Required(), new EditField(EditField::NUMBER)),
            (new IntField('pos_top', 'posTop'))->addFlags(new Required(), new EditField(EditField::NUMBER)),
            (new JsonField('svg_shape', 'svgShape'))->addFlags(new VueComponent('moorl-svg-shape')),
            (new ManyToOneAssociationField('combinationDiscount', 'moorl_image_map_id', ImageMapDefinition::class, 'id'))->addFlags(new EditField(), new LabelProperty('name')),
            (new ManyToOneAssociationField('productStream', 'product_stream_id', ProductStreamDefinition::class, 'id'))->addFlags(),
            (new ManyToOneAssociationField('product', 'product_id', ProductDefinition::class, 'id'))->addFlags(new EditField(), new LabelProperty('name')),
            (new ManyToOneAssociationField('category', 'category_id', CategoryDefinition::class, 'id'))->addFlags()
        ]);
    }
}
