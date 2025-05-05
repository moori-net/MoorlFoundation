<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\EmbeddedMedia;

use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\DataAbstractionLayer\MappingEntityDefinition;
use Shopware\Core\System\Language\LanguageDefinition;

class EmbeddedMediaLanguageDefinition extends MappingEntityDefinition
{
    final public const ENTITY_NAME = 'moorl_media_language';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new FkField('moorl_media_id', 'embeddedMediaId', EmbeddedMediaDefinition::class))->addFlags(new PrimaryKey(), new Required()),
            (new FkField('language_id', 'languageId', LanguageDefinition::class))->addFlags(new PrimaryKey(), new Required()),
            new ManyToOneAssociationField('embeddedMedia', 'moorl_media_id', EmbeddedMediaDefinition::class),
            new ManyToOneAssociationField('language', 'language_id', LanguageDefinition::class)
        ]);
    }
}
