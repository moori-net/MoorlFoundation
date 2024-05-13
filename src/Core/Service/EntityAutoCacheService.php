<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Service;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use MoorlFoundation\Core\System\EntityAutoCacheInterface;
use Psr\Log\LoggerInterface;
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
    public const MAIN_ID_FIELD = 'main_id_field';
    public const REFERENCE_ID_FIELD = 'reference_id_field';
    public const RELATED_ENTITY = 'related_entity';
    public const MAIN_ENTITY = 'main_entity';
    public const ACTIVE = 'active';
    public const PRODUCT_STREAM = 'product_stream';
    public const ENTITY_ASSOCIATION = 'entity_assoc';
    public const START_TIME = 'start_time';
    public const END_TIME = 'end_time';
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
        private readonly LoggerInterface $logger,
        private readonly iterable $entityDefinitions,
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

        try {
            $this->scanForTimeControlledEntities(self::TRIGGER_LIVE);
        } catch (\Throwable $exception) {
            $this->logger->critical(
                sprintf("Error while scanning for time-controlled entities with message: %s", $exception->getMessage())
            );
        }
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

        try {
            $this->updateProductStream($entityDefinition, $ids, $options, $context);
        } catch (\Throwable $exception) {
            $this->logger->critical(
                sprintf("Error while updating product stream with message: %s", $exception->getMessage()),
                [
                    'entityName' => $entityDefinition->getEntityName(),
                    'ids' => $ids,
                    'options' => $options
                ]
            );
        }

        try {
            $this->updateCms($ids, $options, $context);
        } catch (\Throwable $exception) {
            $this->logger->critical(
                sprintf("Error while updating cms with message: %s", $exception->getMessage()),
                [
                    'entityName' => $entityDefinition->getEntityName(),
                    'ids' => $ids,
                    'options' => $options
                ]
            );
        }

        try {
            $this->updateEntityAssoc($entityDefinition, $ids, $options, $context);
        } catch (\Throwable $exception) {
            $this->logger->critical(
                sprintf("Error while updating entity associations with message: %s", $exception->getMessage()),
                [
                    'entityName' => $entityDefinition->getEntityName(),
                    'ids' => $ids,
                    'options' => $options
                ]
            );
        }

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
            if (!empty($productIds)) {
                $this->writeLine(sprintf("Processing invalidate product with ids %s", json_encode($productIds)));
                $this->eventDispatcher->dispatch(new ProductIndexerEvent(array_unique(array_filter($productIds)), $context));
            }
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

        foreach ($assocOptions as $assocOption) {
            if (!isset($assocOption[self::MAIN_ENTITY])) {
                $assocOption[self::MAIN_ENTITY] =$entityDefinition->getEntityName();
                $assocOption[self::REFERENCE_ID_FIELD] = $this->makeId();
            }

            if (in_array($assocOption[self::MAIN_ENTITY], $this->updatedEntities)) {
                $this->writeTitle(sprintf("The entity %s was already refreshed", $assocOption[self::MAIN_ENTITY]));
                continue;
            }

            $this->writeLine(sprintf("Current assoc option: %s", json_encode($assocOption)));

            if (isset($assocOption[self::RELATED_ENTITY])) {
                $this->writeTitle(sprintf("Related entity %s", $assocOption[self::RELATED_ENTITY]));

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
                    $upsertCommands[] = $this->makeUpsertCommand($assocOption, $ids, $context);
                }

                continue;
            }

            if (!isset($assocOption[self::MAIN_ID_FIELD])) {
                $assocOption[self::MAIN_ID_FIELD] = $this->makeId($entityDefinition->getEntityName());
            }
            if (!isset($assocOption[self::REFERENCE_ID_FIELD])) {
                $assocOption[self::REFERENCE_ID_FIELD] = $this->makeId();
            }

            $upsertCommands[] = $this->makeUpsertCommand($assocOption, $ids, $context, $entityDefinition->getEntityName());
        }

        if (!empty($productIds)) {
            $this->writeLine(sprintf("Processing invalidate product with ids %s", json_encode($productIds)));
            $this->eventDispatcher->dispatch(new ProductIndexerEvent(array_unique($productIds), $context));
        }
        if (!empty($categoryIds)) {
            $this->writeLine(sprintf("Processing invalidate category with ids %s", json_encode($categoryIds)));
            $this->eventDispatcher->dispatch(new CategoryIndexerEvent(array_unique($categoryIds), $context));
        }
        if (!empty($upsertCommands)) {
            foreach ($upsertCommands as $upsertCommand) {
                if (!isset($upsertCommand[self::ENTITY])) {
                    continue;
                }
                if (empty($upsertCommand[self::PAYLOAD])) {
                    continue;
                }

                $this->writeLine(sprintf(
                    "Processing refresh %s with payload %s",
                    $upsertCommand[self::ENTITY],
                    json_encode($upsertCommand[self::PAYLOAD])
                ));
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
        if (!empty($categoryIds)) {
            $this->writeLine(sprintf("Processing invalidate category with ids %s", json_encode($categoryIds)));
            $this->eventDispatcher->dispatch(new CategoryIndexerEvent(array_unique(array_filter($categoryIds)), $context));
        }
    }

    private function getSelectIds(array $assocOption, array $ids, string $format): array
    {
        $this->writeLine(sprintf("Find ids with option %s", json_encode($assocOption)));

        if (isset($assocOption[self::RELATED_ENTITY]) && isset($assocOption[self::ENTITY])) {
            $sql = sprintf(
                "SELECT LOWER(HEX(`%s`)) AS `id` FROM `%s` WHERE `%s` IN (:ids) AND `%s` = '%s';",
                $assocOption[self::MAIN_ID_FIELD],
                $assocOption[self::MAIN_ENTITY],
                $assocOption[self::REFERENCE_ID_FIELD],
                $assocOption[self::ENTITY],
                $assocOption[self::RELATED_ENTITY]
            );
        } else {
            $sql = sprintf(
                "SELECT LOWER(HEX(`%s`)) AS `id` FROM `%s` WHERE `%s` IN (:ids);",
                $assocOption[self::MAIN_ID_FIELD],
                $assocOption[self::MAIN_ENTITY],
                $assocOption[self::REFERENCE_ID_FIELD]
            );
        }

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

    private function makeUpsertCommand(array $assocOption, array $ids, Context $context, ?string $entityName = null): array
    {
        $entityIds = $this->getSelectIds($assocOption, $ids, self::FLAT);
        if (empty($entityIds)) {
            return [];
        }
        if (isset($assocOption[self::RELATED_ENTITY]) && isset($assocOption[self::ENTITY])) {
            $entityRepository = $this->definitionInstanceRegistry->getRepository($assocOption[self::RELATED_ENTITY]);
            $criteria = new Criteria($entityIds);
            $entityIds = $entityRepository->searchIds($criteria, $context)->getIds();
        }
        if (empty($entityIds)) {
            return [];
        }
        return [
            self::ENTITY => $entityName ?: $assocOption[self::RELATED_ENTITY],
            self::PAYLOAD => \array_map(static fn (string $id): array => ['id' => $id], \array_values($entityIds))
        ];
    }

    private function writeTitle(string $text): void
    {
        $this->console?->title($text);
        $this->logger->debug($text);
    }

    private function writeLine(string $text): void
    {
        $this->console?->writeln($text);
        $this->console?->writeln("");
        $this->logger->debug($text);
    }
}
