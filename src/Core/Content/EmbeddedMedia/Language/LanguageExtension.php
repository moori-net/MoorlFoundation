<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\EmbeddedMedia\Language;

use MoorlFoundation\Core\Content\EmbeddedMedia\EmbeddedMediaDefinition;
use MoorlFoundation\Core\Content\EmbeddedMedia\EmbeddedMediaLanguageDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityExtension;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\System\Language\LanguageDefinition;

class LanguageExtension extends EntityExtension
{
    public function extendFields(FieldCollection $collection): void
    {
        $collection->add(
            (new ManyToManyAssociationField(
                EmbeddedMediaDefinition::EXTENSION_COLLECTION_NAME,
                EmbeddedMediaDefinition::class,
                EmbeddedMediaLanguageDefinition::class,
                'language_id',
                'moorl_media_id'
            ))
        );
    }

    public function getEntityName(): string
    {
        return LanguageDefinition::ENTITY_NAME;
    }
}
