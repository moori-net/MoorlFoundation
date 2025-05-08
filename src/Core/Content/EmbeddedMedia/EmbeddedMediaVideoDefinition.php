<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\EmbeddedMedia;

use Shopware\Core\Content\Media\MediaDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\RestrictDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class EmbeddedMediaVideoDefinition extends EntityDefinition
{
    final public const ENTITY_NAME = 'moorl_media_video';
    final public const COLLECTION_NAME = 'videos';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return EmbeddedMediaVideoCollection::class;
    }

    public function getEntityClass(): string
    {
        return EmbeddedMediaVideoEntity::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),
            (new FkField('moorl_media_id', 'embeddedMediaId', EmbeddedMediaDefinition::class))->addFlags(new Required()),
            (new FkField('media_id', 'mediaId', MediaDefinition::class))->addFlags(new Required()),
            (new StringField('min_width', 'minWidth'))->addFlags(),

            (new ManyToOneAssociationField(
                'media',
                'media_id',
                MediaDefinition::class
            ))->addFlags(new RestrictDelete()),

            (new ManyToOneAssociationField(
                EmbeddedMediaDefinition::PROPERTY_NAME,
                'moorl_media_id',
                EmbeddedMediaDefinition::class
            ))->addFlags(),
        ]);
    }
}
