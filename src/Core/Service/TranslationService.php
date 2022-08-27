<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Service;

use DeepL\TranslateTextOptions;
use DeepL\Translator;
use MoorlFoundation\Core\System\EntityTranslationInterface;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\DataAbstractionLayer\EntityWriteResult;
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
    private const HASH_KEY = 'moorl_trans_hash';
    private string $sourceLocale = 'de-DE';
    /**
     * @var iterable<EntityTranslationInterface>
     */
    private iterable $entityTranslations;

    public function __construct(
        SystemConfigService $systemConfigService,
        DefinitionInstanceRegistry $definitionInstanceRegistry,
        iterable $entityTranslations
    )
    {
        $this->systemConfigService = $systemConfigService;
        $this->definitionInstanceRegistry = $definitionInstanceRegistry;
        $this->entityTranslations = $entityTranslations;
    }

    public function translate(string $entityName, array $writeResults, Context $context): void
    {
        $ids = array_map(fn(EntityWriteResult $writeResult): ?string =>
            \is_array($writeResult->getPrimaryKey()) ? null : $writeResult->getPrimaryKey()
        , $writeResults);
        if (empty($ids)) {
            return;
        }

        if ($context->hasState(self::HASH_KEY)) {
            return;
        }

        foreach ($this->entityTranslations as $entityTranslation) {
            if ($entityName === $entityTranslation->getEntityName()) {
                $this->translateAny($ids, $entityTranslation->getConfigKey(), $entityTranslation->getEntityName());
            }
        }
    }

    public function translateAny(array $ids, string $configKey, string $entityName): void
    {
        if (!$this->init()) {
            return;
        }

        $properties = $this->systemConfigService->get($configKey);
        if (!$properties) {
            return;
        }

        $criteria = new Criteria($ids);
        $repository = $this->definitionInstanceRegistry->getRepository($entityName);
        $items = $repository->search($criteria, $this->context)->getEntities();

        $payload = $this->translateItems($items, $properties);

        if ($payload) {
            $repository->upsert($payload, $this->context);
        }
    }

    public function translateItems(EntityCollection $items, array $properties): array
    {
        $translateDestination = $this->systemConfigService->get('MoorlFoundation.config.translateDestination');

        $payload = [];
        foreach ($items as $item) {
            $sources = $item->getTranslated();
            $sources = array_filter($sources, fn($k) => in_array($k, $properties), ARRAY_FILTER_USE_KEY);
            $sourcesHash = md5(json_encode($sources));
            $customFields = $item->getCustomFields() ?: [];
            if (empty($customFields[self::HASH_KEY]) || $customFields[self::HASH_KEY] !== $sourcesHash) {
                $translations = [];
                foreach ($translateDestination as $languageId) {
                    $translations[$languageId] = [];
                    foreach ($sources as $k => $v) {
                        $translations[$languageId][$k] = $this->translateField($v, $this->languages[$languageId]);
                    }
                }
                $payload[] = [
                    'id' => $item->getId(),
                    'translations' => $translations,
                    'customFields' => [
                        self::HASH_KEY => $sourcesHash
                    ]
                ];
            }
        }

        return $payload;
    }

    private function translateField(string $text, string $destinationLocale): string
    {
        $textResult = $this->translator->translateText(
            $text,
            substr($this->sourceLocale, 0, 2),
            substr($destinationLocale, 0, 2),
            [
                TranslateTextOptions::TAG_HANDLING => 'html',
                TranslateTextOptions::IGNORE_TAGS => 'creator,author,name,brand,manufacturer',
                TranslateTextOptions::FORMALITY => $this->systemConfigService->get('MoorlFoundation.config.translateFormality') ?: 'default'
            ]
        );

        return (string) $textResult;
    }

    private function init(): bool
    {
        if ($this->translator) {
            return true;
        }

        if (!$this->systemConfigService->get('MoorlFoundation.config.deeplApiKey')) {
            return false;
        }

        if (!$this->systemConfigService->get('MoorlFoundation.config.translateDestination')) {
            return false;
        }

        $this->context = new Context(
            new SystemSource(),
            [],
            Defaults::CURRENCY,
            [$this->systemConfigService->get('MoorlFoundation.config.translateSource'), Defaults::LANGUAGE_SYSTEM]
        );

        $this->context->addState(self::HASH_KEY);

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

        $this->sourceLocale = $this->languages[$this->context->getLanguageId()];

        return true;
    }
}
