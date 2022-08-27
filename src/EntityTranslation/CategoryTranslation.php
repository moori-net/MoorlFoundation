<?php declare(strict_types=1);

namespace MoorlFoundation\EntityTranslation;

use MoorlFoundation\Core\System\EntityTranslationInterface;
use Shopware\Core\Content\Category\CategoryDefinition;

class CategoryTranslation implements EntityTranslationInterface
{
    public function getConfigKey(): string
    {
        return 'MoorlFoundation.config.translateCategoryProperties';
    }

    public function getEntityName(): string
    {
        return CategoryDefinition::ENTITY_NAME;
    }
}
