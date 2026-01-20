<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer\Collection;

use MoorlFoundation\Core\Service\PriceCalculatorService;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FloatField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\PriceField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class FieldPriceCalculationCollection extends FieldCollection
{
    public static function getFieldItems(bool $flag = true): array
    {
        if (!$flag) return [];

        return [
            (new BoolField('show_discount', 'showDiscount'))->addFlags(),
            (new StringField('option_type', 'optionType'))->addFlags(),
            new FloatField('option_percentage', 'optionPercentage'),
            (new PriceField('option_price', 'optionPrice'))->addFlags(),
            (new StringField('list_price_source', 'listPriceSource'))->addFlags(new Required()),
            (new StringField('calculation_price_source', 'calculationPriceSource'))->addFlags(new Required()),
        ];
    }

    public static function getDefaults(): array
    {
        return [
            'showDiscount' => false,
            'optionType' => PriceCalculatorService::TYPE_PERCENTAGE,
            'optionPercentage' => 100,
            'calculationPriceSource' => PriceCalculatorService::SOURCE_ORIGIN_PRICE,
            'listPriceSource' => PriceCalculatorService::SOURCE_ORIGIN_LIST_PRICE,
        ];
    }
}
