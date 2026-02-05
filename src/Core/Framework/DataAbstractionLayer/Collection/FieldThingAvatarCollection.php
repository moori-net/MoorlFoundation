<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer\Collection;

use Shopware\Core\Content\Media\MediaDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class FieldThingAvatarCollection extends FieldCollection
{
    public static function getFieldItems(bool $flag = true): array
    {
        if (!$flag) return [];

        return [
            (new FkField('avatar_id', 'avatarId', MediaDefinition::class))->addFlags(new ApiAware()),
            (new ManyToOneAssociationField('avatar', 'avatar_id', MediaDefinition::class, 'id', true))->addFlags(new ApiAware()),
        ];
    }
}
