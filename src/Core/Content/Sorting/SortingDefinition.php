<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\Sorting;

use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Inherited;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\JsonField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\LockedField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslatedField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslationsAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class SortingDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'moorl_sorting';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return SortingEntity::class;
    }

    public function getCollectionClass(): string
    {
        return SortingCollection::class;
    }

    protected function defineFields(): FieldCollection
    {
        $collection = new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),
            new LockedField(),
            (new StringField('url_key', 'key'))->addFlags(new ApiAware(), new Required()),
            (new StringField('entity', 'entity'))->addFlags(new ApiAware(), new Required()),
            (new IntField('priority', 'priority'))->addFlags(new ApiAware(), new Required()),
            (new BoolField('active', 'active'))->addFlags(new Required()),
            (new JsonField('fields', 'fields'))->addFlags(new Required()),
            (new TranslatedField('label'))->addFlags(new ApiAware()),
            (new TranslationsAssociationField(SortingTranslationDefinition::class, 'moorl_sorting_id'))->addFlags(new Inherited(), new Required()),
        ]);

        return $collection;
    }
}
