<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer\Collection;

use MoorlFoundation\Core\Framework\DataAbstractionLayer\Field\Flags\EditField;
use Shopware\Core\Content\Seo\SeoUrl\SeoUrlDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\LongTextField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslatedField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class FieldThingCollection extends FieldCollection
{
    public static function getFieldItems(
        bool $thingBase = true,
        bool $thingPage = true,
        bool $thingMeta = true,
        bool $media = true
    ): array
    {
        return array_merge([
                (new BoolField('active', 'active'))->addFlags(new EditField('switch')),
                (new TranslatedField('teaser'))->addFlags(new EditField('textarea')),
                (new OneToManyAssociationField('seoUrls', SeoUrlDefinition::class, 'foreign_key'))->addFlags(new ApiAware()),
            ],
            $thingBase ? FieldThingBaseCollection::getFieldItems() : [],
            $thingPage ? FieldThingPageCollection::getFieldItems() : [],
            $thingMeta ? FieldThingMetaCollection::getFieldItems() : [],
            $media ? FieldMediaCollection::getFieldItems() : [],
        );
    }

    public static function getTranslatedFieldItems(
        bool $thingBase = true,
        bool $thingPage = true,
        bool $thingMeta = true
    ): array
    {
        return array_merge([
            new LongTextField('teaser', 'teaser')
        ],
            $thingBase ? FieldThingBaseCollection::getTranslatedFieldItems() : [],
            $thingPage ? FieldThingPageCollection::getTranslatedFieldItems() : [],
            $thingMeta ? FieldThingMetaCollection::getTranslatedFieldItems() : [],
        );
    }
}
