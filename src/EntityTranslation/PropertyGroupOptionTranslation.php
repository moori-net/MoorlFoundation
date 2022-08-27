<?php declare(strict_types=1);

namespace MoorlFoundation\EntityTranslation;

use MoorlFoundation\Core\System\EntityTranslationInterface;
use Shopware\Core\Content\Property\Aggregate\PropertyGroupOption\PropertyGroupOptionDefinition;

class PropertyGroupOptionTranslation implements EntityTranslationInterface
{
    public function getConfigKey(): string
    {
        return 'MoorlFoundation.config.translatePropertyGroupOptionProperties';
    }

    public function getEntityName(): string
    {
        return PropertyGroupOptionDefinition::ENTITY_NAME;
    }
}
