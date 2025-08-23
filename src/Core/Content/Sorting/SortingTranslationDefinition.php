<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\Sorting;

use Shopware\Core\Framework\DataAbstractionLayer\EntityTranslationDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class SortingTranslationDefinition extends EntityTranslationDefinition
{
    final public const ENTITY_NAME = 'moorl_sorting_translation';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return SortingTranslationEntity::class;
    }

    public function getCollectionClass(): string
    {
        return SortingTranslationCollection::class;
    }

    protected function getParentDefinitionClass(): string
    {
        return SortingDefinition::class;
    }

    protected function defineFields(): FieldCollection
    {
        $collection = new FieldCollection([
            (new StringField('label', 'label'))->addFlags(new ApiAware(), new Required())
        ]);

        return $collection;
    }
}
