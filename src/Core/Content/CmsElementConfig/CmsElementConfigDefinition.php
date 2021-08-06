<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\CmsElementConfig;

use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\CustomFields;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\JsonField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class CmsElementConfigDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'moorl_cms_element_config';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return CmsElementConfigCollection::class;
    }

    public function getEntityClass(): string
    {
        return CmsElementConfigEntity::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),
            (new StringField('name', 'name'))->addFlags(new Required()),
            (new StringField('type', 'type'))->addFlags(new Required()),
            new JsonField('config', 'config'),
            new JsonField('data', 'data'),
            new CustomFields()
        ]);
    }
}
