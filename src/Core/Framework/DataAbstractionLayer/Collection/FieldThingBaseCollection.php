<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer\Collection;

use MoorlFoundation\Core\Framework\DataAbstractionLayer\Field\Flags\EditField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\AllowHtml;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\SearchRanking;
use Shopware\Core\Framework\DataAbstractionLayer\Field\LongTextField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslatedField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class FieldThingBaseCollection extends FieldCollection
{
    public static function getFieldItems(bool $flag = true): array
    {
        if (!$flag) return [];

        return [
            (new TranslatedField('name'))->addFlags(new Required(), new SearchRanking(SearchRanking::HIGH_SEARCH_RANKING), new EditField(EditField::TEXT)),
            (new TranslatedField('keywords'))->addFlags(new EditField('textarea')),
            (new TranslatedField('description'))->addFlags(new EditField('textarea')),
        ];
    }

    public static function getTranslatedFieldItems(bool $flag = true): array
    {
        if (!$flag) return [];

        return [
            (new StringField('name', 'name'))->addFlags(),
            (new LongTextField('description', 'description'))->addFlags(new AllowHtml(false)),
            new LongTextField('keywords', 'keywords')
        ];
    }
}
