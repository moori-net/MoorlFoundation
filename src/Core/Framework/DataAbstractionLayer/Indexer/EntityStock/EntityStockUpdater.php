<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer\Indexer\EntityStock;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Shopware\Core\Checkout\Cart\Event\CheckoutOrderPlacedEvent;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemDefinition;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemEntity;
use Shopware\Core\Checkout\Order\OrderDefinition;
use Shopware\Core\Checkout\Order\OrderEvents;
use Shopware\Core\Checkout\Order\OrderStates;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use Shopware\Core\Framework\DataAbstractionLayer\Doctrine\RetryableQuery;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\DataAbstractionLayer\EntityWriteResult;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Write\Command\ChangeSetAware;
use Shopware\Core\Framework\DataAbstractionLayer\Write\Command\DeleteCommand;
use Shopware\Core\Framework\DataAbstractionLayer\Write\Command\InsertCommand;
use Shopware\Core\Framework\DataAbstractionLayer\Write\Command\UpdateCommand;
use Shopware\Core\Framework\DataAbstractionLayer\Write\Validation\PreWriteValidationEvent;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\StateMachine\Event\StateMachineTransitionEvent;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
class EntityStockUpdater implements EventSubscriberInterface
{
    protected string $propertyNamePlural = "";

    public function __construct(
        protected Connection $connection,
        protected DefinitionInstanceRegistry $definitionInstanceRegistry,
        protected SystemConfigService $systemConfigService,
        protected string $entityName,
        protected string $propertyName,
        ?string $propertyNamePlural = null
    ) {
        if ($propertyNamePlural) {
            $this->propertyNamePlural = $propertyNamePlural;
        } else {
            $this->propertyNamePlural = $propertyName . "s";
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            OrderEvents::ORDER_LINE_ITEM_WRITTEN_EVENT => 'lineItemWritten',
            OrderEvents::ORDER_LINE_ITEM_DELETED_EVENT => 'lineItemWritten',
            CheckoutOrderPlacedEvent::class => 'orderPlaced',
            StateMachineTransitionEvent::class => 'stateChanged',
            PreWriteValidationEvent::class => 'triggerChangeSet',
        ];
    }

    protected function enrichSalesChannelProductCriteria(Criteria $criteria, OrderLineItemEntity $lineItem): void
    {
    }

    public function lineItemWritten(EntityWrittenEvent $event): void
    {
        $entityStockIds = [];

        foreach ($event->getWriteResults() as $result) {
            if ($result->getOperation() === EntityWriteResult::OPERATION_INSERT) {
                $entityStockId = $this->assignEntityStockToLineItem($result, $event->getContext());
                if (!$entityStockId) {
                    continue;
                }

                $entityStockIds[] = $entityStockId;
            }
        }

        if (empty($entityStockIds)) {
            return;
        }
        $entityStockIds = array_filter(array_unique($entityStockIds));
        if (empty($entityStockIds)) {
            return;
        }
        $this->update($entityStockIds, $event->getContext());
    }

    private function assignEntityStockToLineItem(EntityWriteResult $result, Context $context): ?string
    {
        if ($result->getProperty('type') !== LineItem::PRODUCT_LINE_ITEM_TYPE) {
            return null;
        }

        $lineItemId = $result->getPrimaryKey();

        $entityStockId = $this->getEntityStockIdByLineItemId($lineItemId, $context);
        if (!$entityStockId) {
            return null;
        }

        $sql = sprintf(
            "UPDATE `order_line_item` SET `%s` = :entity_stock_id WHERE `id` = :id;",
            $this->propertyName,
        );

        $this->connection->executeStatement(
            $sql,
            [
                'id' => Uuid::fromHexToBytes($lineItemId),
                'entity_stock_id' => Uuid::fromHexToBytes($entityStockId)
            ]
        );

        return $entityStockId;
    }

    public function triggerChangeSet(PreWriteValidationEvent $event): void
    {
        if ($event->getContext()->getVersionId() !== Defaults::LIVE_VERSION) {
            return;
        }

        foreach ($event->getCommands() as $command) {
            if (!$command instanceof ChangeSetAware) {
                continue;
            }
            /** @var ChangeSetAware|InsertCommand|UpdateCommand $command */
            if ($command->getDefinition()->getEntityName() !== OrderLineItemDefinition::ENTITY_NAME) {
                continue;
            }
            if ($command instanceof DeleteCommand) {
                $command->requestChangeSet();

                continue;
            }
            if ($command->hasField('referenced_id') || $command->hasField('product_id') || $command->hasField('quantity')) {
                $command->requestChangeSet();

                continue;
            }
        }
    }

    public function stateChanged(StateMachineTransitionEvent $event): void
    {
        if ($event->getContext()->getVersionId() !== Defaults::LIVE_VERSION) {
            return;
        }

        if ($event->getEntityName() !== OrderDefinition::ENTITY_NAME) {
            return;
        }

        if ($event->getToPlace()->getTechnicalName() === OrderStates::STATE_COMPLETED) {
            $this->decreaseStock($event);
            return;
        }

        if ($event->getFromPlace()->getTechnicalName() === OrderStates::STATE_COMPLETED) {
            $this->increaseStock($event);
            return;
        }

        if ($event->getToPlace()->getTechnicalName() === OrderStates::STATE_CANCELLED || $event->getFromPlace()->getTechnicalName() === OrderStates::STATE_CANCELLED) {
            $lineItems = $this->getLineItemsOfOrder($event->getEntityId());
            $entityStockIds = array_column($lineItems, 'entity_stock_id');
            $this->updateAvailableStockAndSales($entityStockIds, $event->getContext());
            return;
        }
    }

    public function update(array $entityStockIds, Context $context): void
    {
        if ($context->getVersionId() !== Defaults::LIVE_VERSION) {
            return;
        }
        $this->updateAvailableStockAndSales($entityStockIds, $context);
    }

    public function orderPlaced(CheckoutOrderPlacedEvent $event): void
    {
        $lineItems = $this->getLineItemsOfOrder($event->getOrderId());
        $entityStockIds = array_column($lineItems, 'entity_stock_id');

        $this->update($entityStockIds, $event->getContext());
    }

    private function increaseStock(StateMachineTransitionEvent $event): void
    {
        $lineItems = $this->getLineItemsOfOrder($event->getEntityId());
        $entityStockIds = array_column($lineItems, 'entity_stock_id');
        $this->updateStock($lineItems, +1);
        $this->updateAvailableStockAndSales($entityStockIds, $event->getContext());
    }

    private function decreaseStock(StateMachineTransitionEvent $event): void
    {
        $lineItems = $this->getLineItemsOfOrder($event->getEntityId());
        $entityStockIds = array_column($lineItems, 'entity_stock_id');
        $this->updateStock($lineItems, -1);
        $this->updateAvailableStockAndSales($entityStockIds, $event->getContext());
    }

    private function updateAvailableStockAndSales(array $entityStockIds, Context $context): void
    {
        $entityStockIds = array_filter(array_keys(array_flip($entityStockIds)));
        if (empty($entityStockIds)) {
            return;
        }

        $definition = $this->definitionInstanceRegistry->getByEntityName($this->entityName);

        $sql = <<<SQL
SELECT 
    LOWER(HEX(order_line_item.%s)) as entity_stock_id,
    IFNULL(SUM(IF(state_machine_state.technical_name = :completed_state, 0, order_line_item.quantity)),0) as open_quantity,
    IFNULL(SUM(IF(state_machine_state.technical_name = :completed_state, order_line_item.quantity, 0)),0) as sales_quantity

FROM order_line_item

INNER JOIN `order`
    ON `order`.id = order_line_item.order_id
    AND `order`.version_id = order_line_item.order_version_id
INNER JOIN state_machine_state
    ON state_machine_state.id = `order`.state_id
    AND state_machine_state.technical_name <> :cancelled_state

WHERE order_line_item.%s IN (:ids)
    AND order_line_item.type = :type
    AND order_line_item.version_id = :version
    AND order_line_item.product_id IS NOT NULL
    AND order_line_item.%s IS NOT NULL
GROUP BY entity_stock_id;
SQL;
        $sql = sprintf($sql, $this->propertyName, $this->propertyName, $this->propertyName);

        $rows = $this->connection->fetchAllAssociative(
            $sql,
            [
                'type' => LineItem::PRODUCT_LINE_ITEM_TYPE,
                'version' => Uuid::fromHexToBytes($context->getVersionId()),
                'completed_state' => OrderStates::STATE_COMPLETED,
                'cancelled_state' => OrderStates::STATE_CANCELLED,
                'ids' => Uuid::fromHexToBytesList($entityStockIds),
            ],
            [
                'ids' => ArrayParameterType::STRING,
            ]
        );

        if ($definition->getField('pseudoSales')) {
            $sql = <<<SQL
UPDATE `%s`
SET available_stock = stock - pseudo_sales - :open_quantity, sales = :sales_quantity, updated_at = :now
WHERE id = :entity_stock_id
SQL;
        } else {
            $sql = <<<SQL
UPDATE `%s`
SET available_stock = stock - :open_quantity, sales = :sales_quantity, updated_at = :now 
WHERE id = :entity_stock_id;
SQL;
        }

        $sql = sprintf($sql, $this->entityName);

        $update = new RetryableQuery(
            $this->connection,
            $this->connection->prepare($sql)
        );

        $fallback = array_column($rows, 'entity_stock_id');
        $fallback = array_diff($entityStockIds, $fallback);
        foreach ($fallback as $id) {
            $update->execute([
                'entity_stock_id' => Uuid::fromHexToBytes((string) $id),
                'open_quantity' => 0,
                'sales_quantity' => 0,
                'now' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
            ]);
        }

        foreach ($rows as $row) {
            $update->execute([
                'entity_stock_id' => Uuid::fromHexToBytes($row['entity_stock_id']),
                'open_quantity' => $row['open_quantity'],
                'sales_quantity' => $row['sales_quantity'],
                'now' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
            ]);
        }
    }

    private function updateStock(array $lineItems, int $multiplier): void
    {
        $sql = <<<SQL
UPDATE
    `%s` 
SET
    stock = stock + :quantity
WHERE
    product_id = :product_id
    AND product_version_id = :product_version_id
    AND id = :id;
SQL;
        $sql = sprintf($sql, $this->entityName);

        $query = new RetryableQuery($this->connection, $this->connection->prepare($sql));

        foreach ($lineItems as $lineItem) {
            $query->execute([
                'quantity' => (int) $lineItem['quantity'] * $multiplier,
                'product_id' => Uuid::fromHexToBytes($lineItem['referenced_id']),
                'product_version_id' => Uuid::fromHexToBytes(Defaults::LIVE_VERSION),
                'id' => Uuid::fromHexToBytes($lineItem['entity_stock_id']),
            ]);
        }
    }

    private function getLineItemsOfOrder(string $orderId): array
    {
        $query = $this->connection->createQueryBuilder();
        $query->select([
            'referenced_id',
            'quantity',
            sprintf('LOWER(HEX(%s)) AS entity_stock_id', $this->propertyName)
        ]);
        $query->from('order_line_item');
        $query->andWhere('type = :type');
        $query->andWhere('order_id = :id');
        $query->andWhere('version_id = :version');
        $query->andWhere( sprintf('%s IS NOT NULL', $this->propertyName));
        $query->setParameter('id', Uuid::fromHexToBytes($orderId));
        $query->setParameter('version', Uuid::fromHexToBytes(Defaults::LIVE_VERSION));
        $query->setParameter('type', LineItem::PRODUCT_LINE_ITEM_TYPE);

        return $query->execute()->fetchAllAssociative();
    }

    private function getEntityStockIdByLineItemId(string $lineItemId, Context $context): ?string
    {
        $criteria = new Criteria([$lineItemId]);
        $criteria->setLimit(1);
        $criteria->addAssociation('order.orderCustomer.customer');
        $lineItemRepository = $this->definitionInstanceRegistry->getRepository(OrderLineItemDefinition::ENTITY_NAME);
        /** @var OrderLineItemEntity $lineItem */
        $lineItem = $lineItemRepository->search($criteria, $context)->get($lineItemId);
        if (!$lineItem) {
            return null;
        }

        $productId = $lineItem->getReferencedId();
        if (!$productId) {
            return null;
        }

        $criteria = new Criteria([$productId]);
        $criteria->setLimit(1);
        $criteria->addAssociation($this->propertyNamePlural);

        // https://github.com/shopware/platform/issues/2702
        /*
        $criteria->getAssociation($this->propertyNamePlural)->addSorting(
            new FieldSorting('availableStock', FieldSorting::DESCENDING)
        );
        */

        $this->enrichSalesChannelProductCriteria($criteria, $lineItem);

        $productRepository = $this->definitionInstanceRegistry->getRepository(ProductDefinition::ENTITY_NAME);
        /** @var ProductEntity $product */

        $product = $productRepository->search($criteria, $context)->get($productId);
        if (!$product) {
            return null;
        }

        if ($product->getParentId()) {
            $criteria->setIds([$product->getParentId()]);
            $product = $productRepository->search($criteria, $context)->get($product->getParentId());
            if (!$product) {
                return null;
            }
        }

        /** @var EntityCollection $entityStocks */
        $entityStocks = $product->getExtension($this->propertyNamePlural);
        if (!$entityStocks) {
            return null;
        }

        if (method_exists($entityStocks, 'sortByAvailableStock')) {
            $entityStocks->sortByAvailableStock();
        }

        $entityStock = $entityStocks->first();
        if (!$entityStock) {
            return null;
        }
        return $entityStock->getId();
    }
}
