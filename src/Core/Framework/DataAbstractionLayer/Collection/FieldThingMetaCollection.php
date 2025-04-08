<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer\Collection;

use MoorlFoundation\Core\Framework\DataAbstractionLayer\Field\Flags\EditField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\LongTextField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslatedField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class FieldThingMetaCollection extends FieldCollection
{
    public static function getFieldItems(bool $flag = true): array
    {
        if (!$flag) return [];

        return [
            (new TranslatedField('metaTitle'))->addFlags(new EditField('text')),
            (new TranslatedField('metaDescription'))->addFlags(new EditField('textarea')),
            (new TranslatedField('metaKeywords'))->addFlags(new EditField('textarea')),
        ];
    }

    public static function getTranslatedFieldItems(bool $flag = true): array
    {
        if (!$flag) return [];

        return [
            new LongTextField('meta_keywords', 'metaKeywords'),
            new LongTextField('meta_title', 'metaTitle'),
            new LongTextField('meta_description', 'metaDescription')
        ];
    }
}
