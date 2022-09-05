<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer\Collection;

use MoorlFoundation\Core\Framework\DataAbstractionLayer\Field\Flags\EditField;
use MoorlFoundation\Core\Framework\DataAbstractionLayer\Field\Flags\LabelProperty;
use MoorlFoundation\Core\Framework\DataAbstractionLayer\FieldCollectionMergeTrait;
use Shopware\Core\Content\Cms\CmsPageDefinition;
use Shopware\Core\Content\Media\MediaDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\SearchRanking;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslatedField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class FieldThingCollection extends FieldCollection
{
    use FieldCollectionMergeTrait;

    public function __construct()
    {
        return new parent([
            (new BoolField('active', 'active'))->addFlags(new EditField('switch')),
            (new TranslatedField('name'))->addFlags(new Required(), new SearchRanking(SearchRanking::HIGH_SEARCH_RANKING), new EditField('text')),
            (new TranslatedField('teaser'))->addFlags(new EditField('textarea')),
            (new TranslatedField('keywords'))->addFlags(new EditField('textarea')),
            (new TranslatedField('description'))->addFlags(new EditField('textarea')),
            (new TranslatedField('metaTitle'))->addFlags(new EditField('text')),
            (new TranslatedField('metaDescription'))->addFlags(new EditField('textarea')),
            (new TranslatedField('slotConfig'))->addFlags(),
            new FkField('media_id', 'mediaId', MediaDefinition::class),
            (new ManyToOneAssociationField('media', 'media_id', MediaDefinition::class, 'id', true))->addFlags(new EditField(), new LabelProperty('fileName')),
            new FkField('cms_page_id', 'cmsPageId', CmsPageDefinition::class),
            (new ManyToOneAssociationField('cmsPage', 'cms_page_id', CmsPageDefinition::class))->addFlags(),
        ]);
    }
}
