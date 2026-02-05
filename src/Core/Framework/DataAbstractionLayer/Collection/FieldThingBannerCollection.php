<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer\Collection;

use Shopware\Core\Content\Media\MediaDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class FieldThingBannerCollection extends FieldCollection
{
    public static function getFieldItems(bool $flag = true): array
    {
        if (!$flag) return [];

        return [
            (new FkField('banner_id', 'bannerId', MediaDefinition::class))->addFlags(new ApiAware()),
            (new StringField('banner_color', 'bannerColor'))->addFlags(new ApiAware()),
            (new ManyToOneAssociationField('banner', 'banner_id', MediaDefinition::class, 'id', true))->addFlags(new ApiAware()),
        ];
    }
}
