<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\Media;

use MoorlFoundation\Core\Content\EmbeddedMedia\EmbeddedMediaDefinition;
use MoorlFoundation\Core\Content\EmbeddedMedia\EmbeddedMediaVideoDefinition;
use Shopware\Core\Content\Media\MediaDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\RestrictDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class MediaExtension extends EntityExtension
{
    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            (new OneToManyAssociationField(
                EmbeddedMediaVideoDefinition::EXTENSION_COLLECTION_NAME,
                EmbeddedMediaVideoDefinition::class,
                'media_id'
            ))->addFlags(new RestrictDelete()),
        );

        $collection->add(
            (new OneToManyAssociationField(
                EmbeddedMediaDefinition::EXTENSION_COLLECTION_NAME,
                EmbeddedMediaDefinition::class,
                'media_id'
            ))->addFlags(new RestrictDelete()),
        );

        $collection->add(
            (new OneToManyAssociationField(
                EmbeddedMediaDefinition::EXTENSION_COLLECTION_NAME,
                EmbeddedMediaDefinition::class,
                'cover_id'
            ))->addFlags(new RestrictDelete()),
        );
    }
    
    public function getEntityName(): string
    {
        return MediaDefinition::ENTITY_NAME;
    }
}
