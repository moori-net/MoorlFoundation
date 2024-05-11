<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Service;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use MoorlFoundation\Core\System\EntityAutoCacheInterface;
use Shopware\Core\Content\Category\CategoryDefinition;
use Shopware\Core\Content\Category\Event\CategoryIndexerEvent;
use Shopware\Core\Content\Product\Events\ProductIndexerEvent;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Content\ProductStream\Service\ProductStreamBuilderInterface;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenContainerEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\AndFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\OrFilter;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\SalesChannelRequest;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Shopware\Core\Framework\Adapter\Cache\CacheClearer;

class EntityAutoCacheService implements EventSubscriberInterface
{
    public const ENTITY = 'entity';
    public const PAYLOAD = 'payload';
    public const FLAT = 'flat';
    public const MAIN_ID_FIELD = 'entity_pk_field';
    public const REFERENCE_ID_FIELD = 'entity_fk_field';
    public const RELATED_ENTITY = 'related_entity';
    public const MAIN_ENTITY = 'main_entity';
    public const ACTIVE = 'active';
    public const PRODUCT_STREAM = 'product_stream';
    public const ENTITY_ASSOCIATION = 'entity_assoc';
    public const START_TIME = 'start_time';
    public const END_TIME = 'end_time';
    public const TIME_ZONE = 'time_zone';
    public const CMS_SLOT = 'cms_slot';
    public const CMS_SLOT_TYPE = 'cms_slot_type';
    public const CMS_SLOT_CONFIG_KEY = 'cms_slot_config_key';
    public const METHOD_CLEAR = 'clear';
    public const METHOD_UPDATE = 'update';
    public const TRIGGER_LIVE = 'live';
    public const TRIGGER_SCHEDULED = 'scheduled';

    private array $updatedEntities = [];
    private ?SymfonyStyle $console = null;

    /**
     * @param EntityAutoCacheInterface[] $entityDefinitions
     */
    public function __construct(
        private readonly SystemConfigService $systemConfigService,
        private readonly CacheClearer $cacheClearer,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly DefinitionInstanceRegistry $definitionInstanceRegistry,
        private readonly Connection $connection,
        private readonly ProductStreamBuilderInterface $productStreamBuilder,
        private readonly iterable $entityDefinitions
    )
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onRequest',
            EntityWrittenContainerEvent::class => 'onEntityWrittenContainerEvent'
        ];
    }

    public function onRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        if (!$event->getRequest()->attributes->has(SalesChannelRequest::ATTRIBUTE_IS_SALES_CHANNEL_REQUEST)) {
            return;
        }

        $this->scanForTimeControlledEntities(self::TRIGGER_LIVE);
    }

    public function onEntityWrittenContainerEvent(EntityWrittenContainerEvent $event): void
    {
        if ($this->systemConfigService->get('MoorlFoundation.config.entityAutoCacheSkip')) {
            return;
        }

        foreach ($this->entityDefinitions as $entityDefinition) {
            $entityEvent = $event->getEventByEntityName($entityDefinition->getEntityName());
            if ($entityEvent) {
                $this->updateEntity($entityDefinition, $entityEvent->getIds(), $event->getContext());
            }
        }
    }

    public function scanForTimeControlledEntities(?string $config = null, ?SymfonyStyle $console = null): void
    {
        $this->console = $console;

        $trigger = $this->systemConfigService->get('MoorlFoundation.config.entityAutoCacheTrigger') ?: self::TRIGGER_LIVE;
        if ($config && $trigger !== $config) {
            return;
        } else {
            $this->writeTitle(sprintf("Current trigger: %s", $trigger));
        }

        $method = $this->systemConfigService->get('MoorlFoundation.config.entityAutoCacheMethod') ?: self::METHOD_UPDATE;
        $time = (new \DateTimeImmutable())->modify("-10 Second")->format(DATE_ATOM);
        $context = Context::createDefaultContext();
        $cacheClear = false;

        foreach ($this->entityDefinitions as $entityDefinition) {
            $options = $entityDefinition->getEntityAutoCacheOptions();

            if (isset($options[self::START_TIME]) || isset($options[self::END_TIME])) {
                $this->writeTitle(sprintf("Scanning for %s", $entityDefinition->getEntityName()));

                $activeFilterSql = "";
                if (isset($options[self::ACTIVE])) {
                    $activeFilterSql = sprintf("AND `%s` = '1'", $options[self::ACTIVE]);
                }

                $timeRangeFilterSql = [];
                if (isset($options[self::START_TIME])) {
                    $timeRangeFilterSql[] = sprintf(
                        "(`%s` > `updated_at` AND `%s` < '%s')",
                        $options[self::START_TIME],
                        $options[self::START_TIME],
                        $time
                    );
                }
                if (isset($options[self::END_TIME])) {
                    $timeRangeFilterSql[] = sprintf(
                        "(`%s` > `updated_at` AND `%s` < '%s')",
                        $options[self::END_TIME],
                        $options[self::END_TIME],
                        $time
                    );
                }

                $sql = sprintf(
                    "SELECT LOWER(HEX(`id`)) AS `id` FROM `%s` WHERE (%s) %s;",
                    $entityDefinition->getEntityName(),
                    implode(" OR ", $timeRangeFilterSql),
                    $activeFilterSql
                );

                $this->writeLine($sql);

                if ($method === self::METHOD_CLEAR) {
                    $ids = $this->connection->fetchFirstColumn($sql);
                    if (empty($ids)) {
                        continue;
                    }

                    $cacheClear = true;

                    $this->writeTitle(sprintf("Updating ids %s", json_encode($ids)));

                    $sql = sprintf(
                        "UPDATE `%s` SET `updated_at` = '%s' WHERE `id` IN (:ids);",
                        $entityDefinition->getEntityName(),
                        $time
                    );

                    $this->connection->executeStatement(
                        $sql,
                        ['ids' => Uuid::fromHexToBytesList($ids)],
                        ['ids' => ArrayParameterType::BINARY]
                    );
                } else {
                    $payload = $this->connection->fetchAllAssociative($sql);
                    if (empty($payload)) {
                        continue;
                    }

                    $this->writeTitle(sprintf("Updating ids %s", json_encode($payload)));

                    $entityRepository = $this->definitionInstanceRegistry->getRepository($entityDefinition->getEntityName());
                    $entityRepository->upsert($payload, $context);
                }
            }
        }

        if ($cacheClear && $method === self::METHOD_CLEAR) {
            $this->writeTitle("Clearing cache");
            $this->cacheClearer->clear();
            $this->writeLine("Done");
        }
    }

    public function updateEntity(EntityAutoCacheInterface $entityDefinition, array $ids, Context $context): void
    {
        $options = $entityDefinition->getEntityAutoCacheOptions();

        $this->updateProductStream($entityDefinition, $ids, $options, $context);
        $this->updateCms($ids, $options, $context);
        $this->updateEntityAssoc($entityDefinition, $ids, $options, $context);

        $this->updatedEntities[] = $entityDefinition->getEntityName();
    }

    private function updateProductStream(EntityAutoCacheInterface $entityDefinition, array $ids, array $options, Context $context): void
    {
        if (!isset($options[self::PRODUCT_STREAM])) {
            return;
        }

        $entityRepository = $this->definitionInstanceRegistry->getRepository($entityDefinition->getEntityName());
        $criteria = new Criteria($ids);
        /** @var EntityCollection $entities */
        $entities = $entityRepository->search($criteria, $context)->getEntities();
        $productRepository = $this->definitionInstanceRegistry->getRepository(ProductDefinition::ENTITY_NAME);

        /** @var Entity $entity */
        foreach ($entities as $entity) {
            if (!$entity->__isset($options[self::PRODUCT_STREAM])) {
                continue;
            }

            $criteria = new Criteria();
            $filters = $this->productStreamBuilder->buildFilters(
                $entity->__get($options[self::PRODUCT_STREAM]),
                $context
            );
            $criteria->addFilter(...$filters);

            $productIds = $productRepository->searchIds($criteria, $context)->getIds();

            $this->eventDispatcher->dispatch(new ProductIndexerEvent($productIds, $context));
        }
    }

    private function updateEntityAssoc(EntityAutoCacheInterface $entityDefinition, array $ids, array $options, Context $context): void
    {
        if (!isset($options[self::ENTITY_ASSOCIATION])) {
            return;
        }

        $assocOptions = $options[self::ENTITY_ASSOCIATION];
        $productIds = [];
        $categoryIds = [];
        $upsertCommands = [];

        $this->writeTitle(sprintf("Find associations for %s", $entityDefinition->getEntityName()));

        foreach ($assocOptions as $d => $assocOption) {
            if (!isset($assocOption[self::MAIN_ENTITY])) {
                $assocOption[self::MAIN_ENTITY] =$entityDefinition->getEntityName();
                $assocOption[self::REFERENCE_ID_FIELD] = $this->makeId();
            }

            if (in_array($assocOption[self::MAIN_ENTITY], $this->updatedEntities)) {
                $this->writeTitle(sprintf("#%d - The entity %s was already refreshed", $d, $assocOption[self::MAIN_ENTITY]));
                continue;
            }

            $this->writeTitle(sprintf("#%d - Processing association %s", $d, $assocOption[self::MAIN_ENTITY]));

            if (isset($assocOption[self::RELATED_ENTITY])) {
                if (!isset($assocOption[self::MAIN_ID_FIELD])) {
                    $assocOption[self::MAIN_ID_FIELD] = $this->makeId($assocOption[self::RELATED_ENTITY]);
                }
                if (!isset($assocOption[self::REFERENCE_ID_FIELD])) {
                    $assocOption[self::REFERENCE_ID_FIELD] = $this->makeId($entityDefinition->getEntityName());
                }

                if ($assocOption[self::RELATED_ENTITY] === ProductDefinition::ENTITY_NAME) {
                    $productIds = array_merge($productIds, $this->getSelectIds($assocOption, $ids, self::FLAT));
                } elseif ($assocOption[self::RELATED_ENTITY] === CategoryDefinition::ENTITY_NAME) {
                    $categoryIds = array_merge($categoryIds, $this->getSelectIds($assocOption, $ids, self::FLAT));
                } else {
                    $upsertCommands[] = $this->makeUpsertCommand($assocOption, $ids);
                }

                continue;
            }

            if (!isset($assocOption[self::MAIN_ID_FIELD])) {
                $assocOption[self::MAIN_ID_FIELD] = $this->makeId($entityDefinition->getEntityName());
            }
            if (!isset($assocOption[self::REFERENCE_ID_FIELD])) {
                $assocOption[self::REFERENCE_ID_FIELD] = $this->makeId();
            }

            $upsertCommands[] = $this->makeUpsertCommand($assocOption, $ids, $entityDefinition->getEntityName());
        }

        if (!empty($productIds)) {
            $this->writeLine(sprintf("#%d - Processing entity %s", $d, $assocOption[self::MAIN_ENTITY]));
            $this->eventDispatcher->dispatch(new ProductIndexerEvent(array_unique(array_filter($productIds)), $context));
        }
        if (!empty($categoryIds)) {
            $this->eventDispatcher->dispatch(new CategoryIndexerEvent(array_unique(array_filter($categoryIds)), $context));
        }
        if (!empty($upsertCommands)) {
            foreach ($upsertCommands as $upsertCommand) {
                $entityRepository = $this->definitionInstanceRegistry->getRepository($upsertCommand[self::ENTITY]);
                $entityRepository->upsert($upsertCommand[self::PAYLOAD], $context);
            }
        }
    }

    private function updateCms(array $ids, array $options, Context $context): void
    {
        if (!isset($options[self::CMS_SLOT])) {
            return;
        }

        $cmsSlotOptions = $options[self::CMS_SLOT];

        $categoryRepository = $this->definitionInstanceRegistry->getRepository(CategoryDefinition::ENTITY_NAME);

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('active', true));
        $criteria->addFilter(new EqualsFilter('visible', true));

        $orFilters = [];
        foreach ($cmsSlotOptions as $cmsSlotOption) {
            $andFilters = [
                new EqualsFilter(
                    'cmsPage.sections.blocks.slots.type',
                    $cmsSlotOption[self::CMS_SLOT_TYPE]
                )
            ];

            if (isset($cmsSlotOption[self::CMS_SLOT_CONFIG_KEY])) {
                $andFilters[] = new EqualsAnyFilter(
                    sprintf("cmsPage.sections.blocks.slots.config.%s.value", $cmsSlotOption[self::CMS_SLOT_CONFIG_KEY]),
                    $ids
                );
            }

            $orFilters[] = new AndFilter($andFilters);
        }

        $criteria->addFilter(new OrFilter($orFilters));

        $categoryIds = $categoryRepository->searchIds($criteria, $context)->getIds();

        $this->eventDispatcher->dispatch(new CategoryIndexerEvent($categoryIds, $context));
    }

    private function getSelectIds(array $assocOption, array $ids, string $format): array
    {
        $this->writeLine(sprintf("Find ids with option %s", json_encode($assocOption)));

        $sql = sprintf(
            "SELECT LOWER(HEX(`%s`)) AS `id` FROM `%s` WHERE `%s` IN (:ids);",
            $assocOption[self::MAIN_ID_FIELD],
            $assocOption[self::MAIN_ENTITY],
            $assocOption[self::REFERENCE_ID_FIELD]
        );

        $this->writeLine($sql);

        if ($format === self::FLAT) {
            return $this->connection->fetchFirstColumn(
                $sql,
                ['ids' => Uuid::fromHexToBytesList($ids)],
                ['ids' => ArrayParameterType::BINARY]
            );
        } elseif ($format === self::PAYLOAD) {
            return $this->connection->fetchAllAssociative(
                $sql,
                ['ids' => Uuid::fromHexToBytesList($ids)],
                ['ids' => ArrayParameterType::BINARY]
            );
        }

        return [];
    }

    private function makeId(?string $entityName = null): string
    {
        return $entityName ? $entityName . '_id' : 'id';
    }

    private function makeUpsertCommand(array $assocOption, array $ids, ?string $entityName = null): array
    {
        return [
            self::ENTITY => $entityName ?: $assocOption[self::RELATED_ENTITY],
            self::PAYLOAD => $this->getSelectIds($assocOption, $ids, self::PAYLOAD)
        ];
    }

    private function writeTitle(string $text): void
    {
        $this->console?->title($text);
    }

    private function writeLine(string $text): void
    {
        $this->console?->writeln($text);
    }
}
