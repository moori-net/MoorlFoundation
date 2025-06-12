<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer\Collection;

use MoorlFoundation\Core\Framework\DataAbstractionLayer\Field\Flags\EditField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\SearchRanking;
use Shopware\Core\Framework\DataAbstractionLayer\Field\JsonField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\LongTextField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslatedField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class FieldBreadcrumbCollection extends FieldCollection
{
    public static function getFieldItems(bool $flag = true): array
    {
        if (!$flag) return [];

        return [
            new TranslatedField('breadcrumb'),
            (new TranslatedField('breadcrumbPlain'))->addFlags(new ApiAware(), new EditField('text'), new SearchRanking(SearchRanking::HIGH_SEARCH_RANKING)),
        ];
    }

    public static function getTranslatedFieldItems(bool $flag = true): array
    {
        if (!$flag) return [];

        return [
            new LongTextField('breadcrumb_plain', 'breadcrumbPlain'),
            new JsonField('breadcrumb', 'breadcrumb'),
        ];
    }
}
