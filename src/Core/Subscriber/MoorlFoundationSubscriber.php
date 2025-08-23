<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Subscriber;

use MoorlFoundation\Core\Service\TranslationService;
use Shopware\Core\Checkout\Order\Event\OrderStateMachineStateChangeEvent;
use Shopware\Core\Checkout\Order\OrderDefinition;
use Shopware\Core\Content\Media\Event\MediaFileExtensionWhitelistEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenContainerEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenEvent;
use Shopware\Core\System\StateMachine\Aggregation\StateMachineTransition\StateMachineTransitionActions;
use Shopware\Core\System\StateMachine\StateMachineRegistry;
use Shopware\Core\System\StateMachine\Transition;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MoorlFoundationSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly SystemConfigService $systemConfigService,
        private readonly TranslationService $translationService,
        private readonly StateMachineRegistry $stateMachineRegistry
    )
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            MediaFileExtensionWhitelistEvent::class => 'onMediaFileExtensionWhitelist',
            EntityWrittenContainerEvent::class => 'onEntityWrittenContainerEvent',
            'state_enter.order_transaction.state.cancelled' => 'stateChanged',
            'state_enter.order_transaction.state.failed' => 'stateChanged',
        ];
    }

    public function onMediaFileExtensionWhitelist(MediaFileExtensionWhitelistEvent $event)
    {
        $whitelist = $event->getWhitelist();
        $whitelistConfig = $this->systemConfigService->get('MoorlFoundation.config.fileExtensions');
        if ($whitelistConfig) {
            $whitelistConfig = explode(",", $whitelistConfig);
            $whitelistConfig = array_map('trim', $whitelistConfig);
            if (is_array($whitelistConfig)) {
                $whitelist = array_merge($whitelist, $whitelistConfig);
            }
        }
        $event->setWhitelist($whitelist);
    }

    public function onEntityWrittenContainerEvent(EntityWrittenContainerEvent $event): void
    {
        foreach ($event->getEvents() as $entityWrittenEvent) {
            if ($entityWrittenEvent instanceof EntityWrittenEvent) {
                $this->translationService->translate($entityWrittenEvent->getEntityName(), $entityWrittenEvent->getWriteResults(), $entityWrittenEvent->getContext());
            }
        }
    }

    public function stateChanged(OrderStateMachineStateChangeEvent $event): void
    {
        if (!$this->systemConfigService->get('MoorlFoundation.config.orderAutoCancel')) {
            return;
        }

        $order = $event->getOrder();

        $this->stateMachineRegistry->transition(new Transition(
            OrderDefinition::ENTITY_NAME,
            $order->getId(),
            StateMachineTransitionActions::ACTION_CANCEL,
            'stateId'
        ), $event->getContext());
    }
}
