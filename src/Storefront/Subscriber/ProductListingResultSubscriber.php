<?php declare(strict_types=1);

namespace MoorlFoundation\Storefront\Subscriber;

use MoorlFoundation\Core\Service\SalesChannelEntitySearchService;
use Shopware\Core\Content\Product\Events\ProductListingResultEvent;
use Shopware\Core\Content\Product\Events\ProductSuggestResultEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProductListingResultSubscriber implements EventSubscriberInterface
{
    private SalesChannelEntitySearchService $searchService;

    public function __construct(
        SalesChannelEntitySearchService $searchService
    )
    {
        $this->searchService = $searchService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ProductSuggestResultEvent::class => 'onProductSuggestResultEvent'
        ];
    }

    public function onProductSuggestResultEvent(ProductSuggestResultEvent $event): void
    {
        $this->searchService->enrich($event);
    }
}
