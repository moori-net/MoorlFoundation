<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer\Collection;

use Shopware\Core\Content\Cms\CmsPageDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\JsonField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ReferenceVersionField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslatedField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class FieldThingPageCollection extends FieldCollection
{
    public static function getFieldItems(bool $flag = true): array
    {
        if (!$flag) return [];

        return [
            (new TranslatedField('slotConfig'))->addFlags(),
            new FkField('cms_page_id', 'cmsPageId', CmsPageDefinition::class),
            new ReferenceVersionField(CmsPageDefinition::class),
            (new ManyToOneAssociationField('cmsPage', 'cms_page_id', CmsPageDefinition::class))->addFlags(),
        ];
    }

    public static function getTranslatedFieldItems(bool $flag = true): array
    {
        if (!$flag) return [];

        return [
            new JsonField('slot_config', 'slotConfig'),
        ];
    }
}
