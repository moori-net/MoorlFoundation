<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer\Collection;

use MoorlFoundation\Core\Framework\DataAbstractionLayer\FieldCollectionMergeTrait;
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
    use FieldCollectionMergeTrait;

    public function __construct()
    {
        return new parent(self::getFieldItems());
    }

    public static function getFieldItems(): array
    {
        return [
            (new FkField('tax_id', 'taxId', TaxDefinition::class))->addFlags(new ApiAware(), new Required()),
            (new ManyToOneAssociationField('tax', 'tax_id', TaxDefinition::class)),
            (new PriceField('price', 'price'))->addFlags(new Required()),
        ];
    }

    public static function getDefaults(): array
    {
        return [
            'price' => [
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
