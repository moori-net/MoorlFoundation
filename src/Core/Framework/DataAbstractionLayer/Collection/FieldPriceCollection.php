<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer\Collection;

use MoorlFoundation\Core\Framework\DataAbstractionLayer\Field\Flags\EditField;
use MoorlFoundation\Core\Framework\DataAbstractionLayer\Field\Flags\LabelProperty;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\PriceField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\System\Tax\TaxDefinition;

class FieldPriceCollection extends FieldCollection
{
    public static function getFieldItems(bool $flag = true): array
    {
        if (!$flag) return [];

        return [
            (new FkField('tax_id', 'taxId', TaxDefinition::class))->addFlags(new ApiAware(), new Required()),
            (new ManyToOneAssociationField('tax', 'tax_id', TaxDefinition::class))->addFlags(new EditField(), new LabelProperty('name')),
            (new PriceField('price', 'price'))->addFlags(new Required(), new EditField('price')),
        ];
    }

    public static function getDefaults(string $key = 'price'): array
    {
        return [
            $key => [
                sprintf("c%s", Defaults::CURRENCY) => [
                    'net' => 0,
                    'gross' => 0,
                    'linked' => true,
                    'currencyId' => Defaults::CURRENCY
                ]
            ]
        ];
    }
}
