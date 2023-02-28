<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\Client;

use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\JsonField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class ClientDefinition extends EntityDefinition
{
    final public const ENTITY_NAME = 'moorl_client';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getCollectionClass(): string
    {
        return ClientCollection::class;
    }

    public function getEntityClass(): string
    {
        return ClientEntity::class;
    }

    public function getDefaults(): array
    {
        return [
            'active' => false,
            'type' => 'ftp',
            'name' => 'My Client',
            'config' => [],
        ];
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),
            (new BoolField('active', 'active'))->addFlags(new Required()),
            (new StringField('type', 'type'))->addFlags(new Required()),
            (new StringField('name', 'name'))->addFlags(new Required()),
            (new JsonField('config', 'config'))->addFlags(new Required()),
        ]);
    }
}
