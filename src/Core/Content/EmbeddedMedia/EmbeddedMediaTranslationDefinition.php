<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\EmbeddedMedia;

use Shopware\Core\Content\Media\MediaDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityTranslationDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\RestrictDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\LongTextField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class EmbeddedMediaTranslationDefinition extends EntityTranslationDefinition
{
    final public const ENTITY_NAME = 'moorl_media_translation';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return EmbeddedMediaTranslationCollection::class;
    }

    public function getEntityClass(): string
    {
        return EmbeddedMediaTranslationEntity::class;
    }

    protected function getParentDefinitionClass(): string
    {
        return EmbeddedMediaDefinition::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new FkField('cover_id', 'coverId', MediaDefinition::class)),
            (new FkField('media_id', 'mediaId', MediaDefinition::class)),

            (new StringField('name', 'name'))->addFlags(new Required()),
            (new StringField('embedded_id', 'embeddedId'))->addFlags(),
            (new StringField('embedded_url', 'embeddedUrl'))->addFlags(),

            new LongTextField('description', 'description'),

            (new ManyToOneAssociationField(
                'cover',
                'cover_id',
                MediaDefinition::class,
                'id',
                true
            ))->addFlags(new RestrictDelete()),
            (new ManyToOneAssociationField(
                'media',
                'media_id',
                MediaDefinition::class,
                'id',
                true
            ))->addFlags(new RestrictDelete()),
        ]);
    }
}
