<?php declare(strict_types=1);

namespace MoorlFoundation\Storefront\Subscriber;

use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\Routing\Event\SalesChannelContextResolvedEvent;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SalesChannelContextResolvedSubscriber implements EventSubscriberInterface
{
    private SystemConfigService $systemConfigService;
    private EntityRepository $cmsPageRepository;

    public function __construct(
        SystemConfigService $systemConfigService,
        EntityRepository $cmsPageRepository
    )
    {
        $this->systemConfigService = $systemConfigService;
        $this->cmsPageRepository = $cmsPageRepository;
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
