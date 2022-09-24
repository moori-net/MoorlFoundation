<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer\Collection;

use MoorlFoundation\Core\Framework\DataAbstractionLayer\Field\Flags\EditField;
use MoorlFoundation\Core\Framework\DataAbstractionLayer\FieldCollectionMergeTrait;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\SearchRanking;
use Shopware\Core\Framework\DataAbstractionLayer\Field\JsonField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\LongTextField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslatedField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class FieldBreadcrumbCollection extends FieldCollection
{
    use FieldCollectionMergeTrait;

    public function __construct()
    {
        return new parent(self::getFieldItems());
    }

    public static function getFieldItems(): array
    {
        return [
            new TranslatedField('breadcrumb'),
            (new TranslatedField('breadcrumbPlain'))->addFlags(new EditField('text'), new SearchRanking(SearchRanking::HIGH_SEARCH_RANKING)),
        ];
    }

    public static function getTranslatedFieldItems(): array
    {
        return [
            new LongTextField('breadcrumb_plain', 'breadcrumbPlain'),
            new JsonField('breadcrumb', 'breadcrumb'),
        ];
    }
}
