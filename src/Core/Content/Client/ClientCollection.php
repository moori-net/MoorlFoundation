<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\Client;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                       add(ClientEntity $entity)
 * @method void                       set(string $key, ClientEntity $entity)
 * @method ClientEntity[]    getIterator()
 * @method ClientEntity[]    getElements()
 * @method ClientEntity|null get(string $key)
 * @method ClientEntity|null first()
 * @method ClientEntity|null last()
 */
class ClientCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return ClientEntity::class;
    }

    public function getByType(string $type): ?ClientEntity
    {
        return $this->filter(fn(ClientEntity $markerEntity) => $markerEntity->getType() == $type)->first();
    }
}
