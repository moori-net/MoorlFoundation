<?php declare(strict_types=1);

namespace MoorlFoundation\EntityTranslation;

use MoorlFoundation\Core\System\EntityTranslationInterface;
use Shopware\Core\Content\Cms\Aggregate\CmsSlot\CmsSlotDefinition;

class CmsSlotTranslation implements EntityTranslationInterface
{
    public function getConfigKey(): string
    {
        return 'MoorlFoundation.config.translateCmsSlotProperties';
    }

    public function getEntityName(): string
    {
        return CmsSlotDefinition::ENTITY_NAME;
    }
}
