<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer\Collection;

use MoorlFoundation\Core\Framework\DataAbstractionLayer\FieldCollectionMergeTrait;
use Shopware\Core\Content\Cms\CmsPageDefinition;
use Shopware\Core\Content\Media\MediaDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\JsonField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslatedField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class FieldThingPageCollection extends FieldCollection
{
    use FieldCollectionMergeTrait;

    public function __construct()
    {
        return new parent(self::getFieldItems());
    }

    public static function getFieldItems(): array
    {
        return [
            (new TranslatedField('slotConfig'))->addFlags(),
            new FkField('media_id', 'mediaId', MediaDefinition::class),
            new FkField('cms_page_id', 'cmsPageId', CmsPageDefinition::class),
            (new ManyToOneAssociationField('cmsPage', 'cms_page_id', CmsPageDefinition::class))->addFlags(),
        ];
    }

    public static function getTranslatedFieldItems(): array
    {
        return [
            new JsonField('slot_config', 'slotConfig'),
        ];
    }
}
