<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Service;

use Shopware\Core\Content\Category\CategoryDefinition;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class TranslationService
{
    private SystemConfigService $systemConfigService;
    private DefinitionInstanceRegistry $definitionInstanceRegistry;

    public function __construct(
        SystemConfigService $systemConfigService,
        DefinitionInstanceRegistry $definitionInstanceRegistry
    )
    {
        $this->systemConfigService = $systemConfigService;
        $this->definitionInstanceRegistry = $definitionInstanceRegistry;
    }

    public function translate(string $entityName, array $ids): void
    {
        switch ($entityName) {
            case CategoryDefinition::ENTITY_NAME:
                $this->translateCategory($ids);
                break;
            case ProductDefinition::ENTITY_NAME:
                $this->translateProduct($ids);
                break;
        }
    }

    private function translateProduct(array $ids): void
    {

    }

    private function translateCategory(array $ids): void
    {

    }
}
