<?php declare(strict_types=1);

namespace MoorlFoundation\Storefront\Subscriber;

use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\Routing\Event\SalesChannelContextResolvedEvent;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SalesChannelContextResolvedSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly SystemConfigService $systemConfigService, private readonly EntityRepository $cmsPageRepository)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SalesChannelContextResolvedEvent::class => 'onSalesChannelContextResolvedEvent',
        ];
    }

    public function onSalesChannelContextResolvedEvent(SalesChannelContextResolvedEvent $event): void
    {
    }
}
