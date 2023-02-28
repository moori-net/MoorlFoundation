<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer\Collection;

use MoorlFoundation\Core\Framework\DataAbstractionLayer\Field\Flags\EditField;
use MoorlFoundation\Core\Framework\DataAbstractionLayer\FieldCollectionMergeTrait;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\AllowHtml;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\SearchRanking;
use Shopware\Core\Framework\DataAbstractionLayer\Field\LongTextField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslatedField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class FieldThingBaseCollection extends FieldCollection
{
    use FieldCollectionMergeTrait;

    public function __construct()
    {
        return new parent(self::getFieldItems());
    }

    public static function getFieldItems(): array
    {
        return [
            (new TranslatedField('name'))->addFlags(new Required(), new SearchRanking(SearchRanking::HIGH_SEARCH_RANKING), new EditField('text')),
            (new TranslatedField('keywords'))->addFlags(new EditField('textarea')),
            (new TranslatedField('description'))->addFlags(new EditField('textarea')),
        ];
    }

    public static function getTranslatedFieldItems(): array
    {
        return [
            (new StringField('name', 'name'))->addFlags(new Required()),
            (new LongTextField('description', 'description'))->addFlags(new AllowHtml(false)),
            new LongTextField('keywords', 'keywords')
        ];
    }
}
