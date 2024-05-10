<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Service;

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
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class EntityAutoCacheService implements EventSubscriberInterface
{
    public const OPT_ACTIVE = 'active';
    public const OPT_PRODUCT_STREAM = 'product_stream';
    public const OPT_START_TIME = 'start_time';
    public const OPT_END_TIME = 'end_time';
    public const OPT_TIMEZONE = 'timezone';
    public const OPT_CMS_SLOT = 'cms_slot';
    public const OPT_CMS_SLOT_TYPE = 'cms_slot_type';
    public const OPT_CMS_SLOT_CONFIG_KEY = 'cms_slot_config_key';

    /**
     * @param EntityAutoCacheInterface[] $entityDefinitions
     */
    public function __construct(
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
        $time = (new \DateTimeImmutable())->format(DATE_ATOM);
        $context = Context::createDefaultContext();

        foreach ($this->entityDefinitions as $entityDefinition) {
            $options = $entityDefinition->getEntityAutoCacheOptions();

            if (isset($options[self::OPT_START_TIME]) || isset($options[self::OPT_END_TIME])) {
                $activeFilterSql = "";
                if (isset($options[self::OPT_ACTIVE])) {
                    $activeFilterSql = sprintf("AND `%s` = '1'", $options[self::OPT_ACTIVE]);
                }

                $timeRangeFilterSql = [];
                if (isset($options[self::OPT_START_TIME])) {
                    $timeRangeFilterSql[] = sprintf(
                        "(`%s` > `updated_at` AND `%s` < '%s')",
                        $options[self::OPT_START_TIME],
                        $options[self::OPT_START_TIME],
                        $time,
                    );
                }
                if (isset($options[self::OPT_END_TIME])) {
                    $timeRangeFilterSql[] = sprintf(
                        "(`%s` > `updated_at` AND `%s` < '%s')",
                        $options[self::OPT_END_TIME],
                        $options[self::OPT_END_TIME],
                        $time,
                    );
                }

                $sql = sprintf(
                    "SELECT LOWER(HEX(`id`)) AS `id` FROM `%s` WHERE (%s) %s;",
                    $entityDefinition->getEntityName(),
                    implode(" OR ", $timeRangeFilterSql),
                    $activeFilterSql
                );

                $payload = $this->connection->fetchAllAssociative($sql);

                if (!empty($payload)) {
                    $entityRepository = $this->definitionInstanceRegistry->getRepository($entityDefinition->getEntityName());
                    $entityRepository->upsert($payload, $context);
                }
            }
        }
    }

    public function onEntityWrittenContainerEvent(EntityWrittenContainerEvent $event): void
    {
        foreach ($this->entityDefinitions as $entityDefinition) {
            $entityEvent = $event->getEventByEntityName($entityDefinition->getEntityName());
            if ($entityEvent) {
                $this->updateEntity($entityDefinition, $entityEvent->getIds(), $event->getContext());
            }
        }
    }

    private function updateEntity(EntityAutoCacheInterface $entityDefinition, array $ids, Context $context): void
    {
        $options = $entityDefinition->getEntityAutoCacheOptions();

        if (isset($options[self::OPT_PRODUCT_STREAM])) {
            $entityRepository = $this->definitionInstanceRegistry->getRepository($entityDefinition->getEntityName());
            $criteria = new Criteria($ids);
            /** @var EntityCollection $entities */
            $entities = $entityRepository->search($criteria, $context)->getEntities();

            $this->updateProductStream($entities, $options, $context);
        }

        if (isset($options[self::OPT_CMS_SLOT])) {
            $this->updateCms($ids, $options, $context);
        }
    }

    private function updateProductStream(EntityCollection $entities, array $options, Context $context): void
    {
        $productRepository = $this->definitionInstanceRegistry->getRepository(ProductDefinition::ENTITY_NAME);

        /** @var Entity $entity */
        foreach ($entities as $entity) {
            if (!$entity->__isset($options[self::OPT_PRODUCT_STREAM])) {
                continue;
            }

            $criteria = new Criteria();
            $filters = $this->productStreamBuilder->buildFilters(
                $entity->__get($options[self::OPT_PRODUCT_STREAM]),
                $context
            );
            $criteria->addFilter(...$filters);

            $productIds = $productRepository->searchIds($criteria, $context)->getIds();

            $this->eventDispatcher->dispatch(new ProductIndexerEvent($productIds, $context));
        }
    }

    private function updateCms(array $ids, array $options, Context $context): void
    {
        $cmsSlotOptions = $options[self::OPT_CMS_SLOT];

        $categoryRepository = $this->definitionInstanceRegistry->getRepository(CategoryDefinition::ENTITY_NAME);

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('active', true));
        $criteria->addFilter(new EqualsFilter('visible', true));

        $orFilters = [];
        foreach ($cmsSlotOptions as $cmsSlotOption) {
            $andFilters = [
                new EqualsFilter(
                    'cmsPage.sections.blocks.slots.type',
                    $cmsSlotOption[self::OPT_CMS_SLOT_TYPE]
                )
            ];

            if (isset($cmsSlotOption[self::OPT_CMS_SLOT_CONFIG_KEY])) {
                $andFilters[] = new EqualsAnyFilter(
                    sprintf("cmsPage.sections.blocks.slots.config.%s.value", $cmsSlotOption[self::OPT_CMS_SLOT_CONFIG_KEY]),
                    $ids
                );
            }

            $orFilters[] = new AndFilter($andFilters);
        }

        $criteria->addFilter(new OrFilter($orFilters));

        $categoryIds = $categoryRepository->searchIds($criteria, $context)->getIds();

        $this->eventDispatcher->dispatch(new CategoryIndexerEvent($categoryIds, $context));
    }
}
