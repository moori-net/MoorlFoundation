<?php declare(strict_types=1);

namespace MoorlFoundation\Storefront\Subscriber;

use Shopware\Core\Content\Cms\CmsPageEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Routing\Event\SalesChannelContextResolvedEvent;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SalesChannelContextResolvedSubscriber implements EventSubscriberInterface
{
    private SystemConfigService $systemConfigService;
    private EntityRepositoryInterface $cmsPageRepository;

    public function __construct(
        SystemConfigService $systemConfigService,
        EntityRepositoryInterface $cmsPageRepository
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
        return;

        $salesChannelContext = $event->getSalesChannelContext();
        $salesChannelId = $salesChannelContext->getSalesChannelId();

        $moorlFoundationListingConfig = $this->systemConfigService->get(
            'MoorlFoundation.config.moorlFoundationListingConfig',
            $salesChannelId
        );

        if (!$moorlFoundationListingConfig) {
            return;
        }

        $criteria = new Criteria([$moorlFoundationListingConfig]);
        $criteria->setLimit(1);
        $criteria->addAssociation('sections.blocks.slots');

        /** @var CmsPageEntity $cmsPage */
        $cmsPage = $this->cmsPageRepository->search($criteria, $salesChannelContext->getContext())->get($moorlFoundationListingConfig);
        if (!$cmsPage) {
            return;
        }

        foreach ($cmsPage->getSections() as $section) {
            foreach ($section->getBlocks() as $block) {
                foreach ($block->getSlots() as $slot) {
                    $config = $slot->getConfig();
                    if (!empty($config['listingLayout'])) {
                        $salesChannelContext->assign(['moorlFoundationListingConfig' => $config]);
                        return;
                    }
                }
            }
        }
    }
}
