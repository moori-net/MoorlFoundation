<?php declare(strict_types=1);

namespace MoorlFoundation\EntityTranslation;

use MoorlFoundation\Core\System\EntityTranslationInterface;
use Shopware\Core\Content\Product\ProductDefinition;

class ProductTranslation implements EntityTranslationInterface
{
    public function getConfigKey(): string
    {
        return 'MoorlFoundation.config.translateProductProperties';
    }

    public function getEntityName(): string
    {
        return ProductDefinition::ENTITY_NAME;
    }
}
