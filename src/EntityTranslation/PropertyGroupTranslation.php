<?php declare(strict_types=1);

namespace MoorlFoundation\EntityTranslation;

use MoorlFoundation\Core\System\EntityTranslationInterface;
use Shopware\Core\Content\Property\PropertyGroupDefinition;

class PropertyGroupTranslation implements EntityTranslationInterface
{
    public function getConfigKey(): string
    {
        return 'MoorlFoundation.config.translatePropertyGroupProperties';
    }

    public function getEntityName(): string
    {
        return PropertyGroupDefinition::ENTITY_NAME;
    }
}
