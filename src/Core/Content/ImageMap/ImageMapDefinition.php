<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\ImageMap;

use Shopware\Core\Content\Media\MediaDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\CustomFields;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\JsonField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslatedField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslationsAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class ImageMapDefinition extends EntityDefinition
{
    final public const ENTITY_NAME = 'moorl_image_map';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return ImageMapCollection::class;
    }

    public function getEntityClass(): string
    {
        return ImageMapEntity::class;
    }

    public function getDefaults(): array
    {
        return [
            'active' => false
        ];
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),
            new FkField('media_id', 'mediaId', MediaDefinition::class),
            (new BoolField('active', 'active'))->addFlags(),
            (new TranslatedField('name'))->addFlags(),
            (new TranslatedField('description'))->addFlags(),
            new CustomFields(),
            new JsonField('options', 'options'),
            new ManyToOneAssociationField('media', 'media_id', MediaDefinition::class, 'id', true),
            new OneToManyAssociationField('items', ImageMapItemDefinition::class, 'moorl_image_map_id'),
            (new TranslationsAssociationField(ImageMapTranslationDefinition::class, 'moorl_image_map_id'))->addFlags(new Required()),
        ]);
    }
}
