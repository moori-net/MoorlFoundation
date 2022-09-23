<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer\Updater\EntityStock;

use Doctrine\DBAL\Connection;
use Moorl\MultiStock\Core\Service\MultiStockService;
use Shopware\Core\Checkout\Cart\Event\CheckoutOrderPlacedEvent;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Order\Aggregate\OrderLineItem\OrderLineItemDefinition;
use Shopware\Core\Checkout\Order\OrderEvents;
use Shopware\Core\Checkout\Order\OrderStates;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Doctrine\RetryableQuery;
use Shopware\Core\Framework\DataAbstractionLayer\EntityWriteResult;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Write\Command\ChangeSetAware;
use Shopware\Core\Framework\DataAbstractionLayer\Write\Command\DeleteCommand;
use Shopware\Core\Framework\DataAbstractionLayer\Write\Command\InsertCommand;
use Shopware\Core\Framework\DataAbstractionLayer\Write\Command\UpdateCommand;
use Shopware\Core\Framework\DataAbstractionLayer\Write\Validation\PreWriteValidationEvent;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\StateMachine\Event\StateMachineTransitionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EntityStockUpdater implements EventSubscriberInterface
{
    private Connection $connection;
    private MultiStockService $service;

    public function __construct(
        Connection $connection,
        MultiStockService $service
    ) {
        $this->connection = $connection;
        $this->service = $service;
    }

    public static function getSubscribedEvents()
    {
        return [
            CheckoutOrderPlacedEvent::class => 'orderPlaced',
            StateMachineTransitionEvent::class => 'stateChanged',
            PreWriteValidationEvent::class => 'triggerChangeSet',
            OrderEvents::ORDER_LINE_ITEM_WRITTEN_EVENT => 'lineItemWritten',
            OrderEvents::ORDER_LINE_ITEM_DELETED_EVENT => 'lineItemWritten',
        ];
    }

    private function assignEntityStockToLineItem(EntityWriteResult $result, Context $context): ?string
    {
        if ($result->getProperty('type') !== LineItem::PRODUCT_LINE_ITEM_TYPE) {
            return null;
        }

        $lineItemId = $result->getPrimaryKey();
        $entityStockId = $this->service->getEntityStockIdByLineItemId($lineItemId, $context);
        if (!$entityStockId) {
            return null;
        }

        $sql = <<<SQL
UPDATE 
    order_line_item 
SET 
    entityStock = :ms_stock_id
WHERE 
    id = :id;
SQL;
        $this->connection->executeStatement ($sql, [
            'id' => Uuid::fromHexToBytes($lineItemId),
            'ms_stock_id' => Uuid::fromHexToBytes($entityStockId),
        ]);

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

    public function stateChanged(StateMachineTransitionEvent $event): void
    {
        if ($event->getContext()->getVersionId() !== Defaults::LIVE_VERSION) {
            return;
        }

        if ($event->getEntityName() !== 'order') {
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
            $entityStockIds = array_column($lineItems, 'ms_stock_id');
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
        $entityStockIds = array_column($lineItems, 'ms_stock_id');
        $this->update($entityStockIds, $event->getContext());
    }

    private function increaseStock(StateMachineTransitionEvent $event): void
    {
        $lineItems = $this->getLineItemsOfOrder($event->getEntityId());
        $entityStockIds = array_column($lineItems, 'ms_stock_id');
        $this->updateStock($lineItems, +1);
        $this->updateAvailableStockAndSales($entityStockIds, $event->getContext());
    }

    private function decreaseStock(StateMachineTransitionEvent $event): void
    {
        $lineItems = $this->getLineItemsOfOrder($event->getEntityId());
        $entityStockIds = array_column($lineItems, 'ms_stock_id');
        $this->updateStock($lineItems, -1);
        $this->updateAvailableStockAndSales($entityStockIds, $event->getContext());
    }

    private function updateAvailableStockAndSales(array $entityStockIds, Context $context): void
    {
        //dump($entityStockIds);exit;

        $entityStockIds = array_filter(array_keys(array_flip($entityStockIds)));
        if (empty($entityStockIds)) {
            return;
        }

        $sql = <<<SQL
SELECT 
    LOWER(HEX(order_line_item.entityStock)) as ms_stock_id,
    IFNULL(SUM(IF(state_machine_state.technical_name = :completed_state, 0, order_line_item.quantity)),0) as open_quantity,
    IFNULL(SUM(IF(state_machine_state.technical_name = :completed_state, order_line_item.quantity, 0)),0) as sales_quantity

FROM order_line_item

INNER JOIN `order`
    ON `order`.id = order_line_item.order_id
    AND `order`.version_id = order_line_item.order_version_id
INNER JOIN state_machine_state
    ON state_machine_state.id = `order`.state_id
    AND state_machine_state.technical_name <> :cancelled_state

WHERE order_line_item.entityStock IN (:ids)
    AND order_line_item.type = :type
    AND order_line_item.version_id = :version
    AND order_line_item.product_id IS NOT NULL
    AND order_line_item.entityStock IS NOT NULL
GROUP BY ms_stock_id;
SQL;
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
                'ids' => Connection::PARAM_STR_ARRAY,
            ]
        );

        $sql = <<<SQL
UPDATE 
    moorl_ms_stock
SET 
    available_stock = stock - :open_quantity, 
    sales = :sales_quantity, 
    updated_at = :now 
WHERE 
    id = :ms_stock_id
SQL;
        $update = new RetryableQuery(
            $this->connection,
            $this->connection->prepare($sql)
        );

        $fallback = array_column($rows, 'ms_stock_id');
        $fallback = array_diff($entityStockIds, $fallback);
        foreach ($fallback as $id) {
            $update->execute([
                'ms_stock_id' => Uuid::fromHexToBytes((string) $id),
                'open_quantity' => 0,
                'sales_quantity' => 0,
                'now' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
            ]);
        }

        foreach ($rows as $row) {
            $update->execute([
                'ms_stock_id' => Uuid::fromHexToBytes($row['ms_stock_id']),
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
    moorl_ms_stock 
SET
    stock = stock + :quantity
WHERE
    product_id = :product_id
    AND product_version_id = :product_version_id
    AND id = :id;
SQL;
        $query = new RetryableQuery($this->connection, $this->connection->prepare($sql));

        foreach ($lineItems as $lineItem) {
            $query->execute([
                'quantity' => (int) $lineItem['quantity'] * $multiplier,
                'product_id' => Uuid::fromHexToBytes($lineItem['referenced_id']),
                'product_version_id' => Uuid::fromHexToBytes(Defaults::LIVE_VERSION),
                'id' => Uuid::fromHexToBytes($lineItem['ms_stock_id']),
            ]);
        }
    }

    private function getLineItemsOfOrder(string $orderId): array
    {
        $query = $this->connection->createQueryBuilder();
        $query->select([
            'referenced_id',
            'quantity',
            'LOWER(HEX(entityStock)) AS ms_stock_id'
        ]);
        $query->from('order_line_item');
        $query->andWhere('type = :type');
        $query->andWhere('order_id = :id');
        $query->andWhere('version_id = :version');
        $query->andWhere('entityStock IS NOT NULL');
        $query->setParameter('id', Uuid::fromHexToBytes($orderId));
        $query->setParameter('version', Uuid::fromHexToBytes(Defaults::LIVE_VERSION));
        $query->setParameter('type', LineItem::PRODUCT_LINE_ITEM_TYPE);

        return $query->execute()->fetchAllAssociative();
    }
}
