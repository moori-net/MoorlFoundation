<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Service;

use DeepL\Translator;
use Shopware\Core\Content\Category\CategoryDefinition;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\Language\LanguageCollection;
use Shopware\Core\System\Language\LanguageDefinition;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class TranslationService
{
    private SystemConfigService $systemConfigService;
    private DefinitionInstanceRegistry $definitionInstanceRegistry;
    private ?Translator $translator = null;
    private Context $context;
    private array $languages = [];

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
        if (!$this->init()) {
            return;
        }

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
        $criteria = new Criteria($ids);
        $repository = $this->definitionInstanceRegistry->getRepository(ProductDefinition::ENTITY_NAME);

        $products = $repository->search($criteria, $this->context)->getEntities();
    }

    private function translateCategory(array $ids): void
    {

    }

    private function init(): bool
    {
        if ($this->translator) {
            return true;
        }

        if (!$this->systemConfigService->get('MoorlFoundation.config.deeplApiKey')) {
            return false;
        }

        $this->context = new Context(
            new SystemSource(),
            [],
            Defaults::CURRENCY,
            [$this->systemConfigService->get('MoorlFoundation.config.translateSource'), Defaults::LANGUAGE_SYSTEM]
        );

        // TODO: Check if unlocker installed

        $this->translator = new Translator($this->systemConfigService->get('MoorlFoundation.config.deeplApiKey'));

        $criteria = new Criteria();
        $criteria->addAssociation('locale');
        $languageRepository = $this->definitionInstanceRegistry->getRepository(LanguageDefinition::ENTITY_NAME);
        /** @var LanguageCollection $languages */
        $languages = $languageRepository->search($criteria, $this->context);
        foreach ($languages as $language) {
            $this->languages[$language->getId()] = $language->getLocale()->getCode();
        }

        return true;
    }
}
