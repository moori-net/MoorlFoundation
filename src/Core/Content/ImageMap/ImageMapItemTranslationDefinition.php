<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\ImageMap;

use Shopware\Core\Framework\DataAbstractionLayer\EntityTranslationDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\LongTextField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class ImageMapItemTranslationDefinition extends EntityTranslationDefinition
{
    final public const ENTITY_NAME = 'moorl_image_map_item_translation';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return ImageMapTranslationCollection::class;
    }

    public function getEntityClass(): string
    {
        return ImageMapTranslationEntity::class;
    }

    protected function getParentDefinitionClass(): string
    {
        return ImageMapDefinition::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new StringField('name', 'name'))->addFlags(new Required()),
            new LongTextField('description', 'description')
        ]);
    }
}
