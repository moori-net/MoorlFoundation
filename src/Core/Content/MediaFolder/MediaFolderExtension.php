<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\MediaFolder;

use MoorlFoundation\Core\Content\EmbeddedMedia\EmbeddedMediaDefinition;
use Shopware\Core\Content\Media\Aggregate\MediaFolder\MediaFolderDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\RestrictDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class MediaFolderExtension extends EntityExtension
{
    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            (new OneToManyAssociationField(
                EmbeddedMediaDefinition::EXTENSION_COLLECTION_NAME,
                EmbeddedMediaDefinition::class,
                'media_folder_id'
            ))->addFlags(new RestrictDelete()),
        );
    }
    
    public function getEntityName(): string
    {
        return MediaFolderDefinition::ENTITY_NAME;
    }
}
