<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer\Collection;

use MoorlFoundation\Core\Framework\DataAbstractionLayer\Field\Flags\EditField;
use MoorlFoundation\Core\Framework\DataAbstractionLayer\Field\Flags\LabelProperty;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ReferenceVersionField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class FieldStockCollection extends FieldCollection
{
    public static function getFieldItems(bool $flag = true): array
    {
        if (!$flag) return [];

        return [
            (new FkField('product_id', 'productId', ProductDefinition::class))->addFlags(new ApiAware(), new Required()),
            (new ReferenceVersionField(ProductDefinition::class))->addFlags(new ApiAware(), new Required()),
            (new ManyToOneAssociationField('product', 'product_id', ProductDefinition::class))->addFlags(new EditField(), new LabelProperty('productNumber')),
            (new IntField('sales', 'sales'))->addFlags(new Required(), new EditField(EditField::NUMBER)),
            (new IntField('stock', 'stock'))->addFlags(new Required(), new EditField(EditField::NUMBER)),
            (new IntField('available_stock', 'availableStock'))->addFlags(new Required(), new EditField(EditField::NUMBER))
        ];
    }

    public static function getDefaults(): array
    {
        return [
            'sales' => 0,
            'stock' => 0,
            'availableStock' => 0
        ];
    }
}
