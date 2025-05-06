<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\EmbeddedMedia;

use Shopware\Core\Content\Media\MediaDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\CustomFields;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\RestrictDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\JsonField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ParentFkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslatedField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslationsAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class EmbeddedMediaDefinition extends EntityDefinition
{
    final public const ENTITY_NAME = 'moorl_media';
    final public const PROPERTY_NAME = 'embeddedMedia';
    final public const COLLECTION_NAME = 'embeddedMedias';
    final public const EXTENSION_COLLECTION_NAME = 'moorlEmbeddedMedias';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return EmbeddedMediaCollection::class;
    }

    public function getEntityClass(): string
    {
        return EmbeddedMediaEntity::class;
    }

    public function getDefaults(): array
    {
        return [
            'type' => 'auto',
            'duration' => 0,
        ];
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),
            (new ParentFkField(self::class))->addFlags(),
            (new FkField('cover_id', 'coverId', MediaDefinition::class)),
            (new FkField('media_id', 'mediaId', MediaDefinition::class)),

            (new BoolField('active', 'active'))->addFlags(),

            (new IntField('duration', 'duration'))->addFlags(),

            (new StringField('technical_name', 'technicalName'))->addFlags(),
            (new StringField('background_color', 'backgroundColor'))->addFlags(),
            (new StringField('type', 'type'))->addFlags(new Required()),
            (new StringField('embedded_id', 'embeddedId'))->addFlags(),
            (new StringField('embedded_url', 'embeddedUrl'))->addFlags(),

            (new TranslatedField('name'))->addFlags(new Required()),
            (new TranslatedField('description')),

            (new JsonField('config', 'config'))->addFlags(),
            (new CustomFields()),

            (new ManyToOneAssociationField(
                'configParent',
                'parent_id',
                self::class
            ))->addFlags(new RestrictDelete()),

            (new ManyToOneAssociationField(
                'cover',
                'cover_id',
                MediaDefinition::class
            ))->addFlags(new RestrictDelete()),

            (new ManyToOneAssociationField(
                'media',
                'media_id',
                MediaDefinition::class
            ))->addFlags(new RestrictDelete()),

            (new TranslationsAssociationField(
                EmbeddedMediaTranslationDefinition::class,
                'moorl_media_id'
            ))->addFlags(new Required()),
        ]);
    }
}
