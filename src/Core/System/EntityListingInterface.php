<?php declare(strict_types=1);

namespace MoorlFoundation\Core\System;

use Shopware\Core\Content\Product\Events\ProductListingResultEvent;
use Shopware\Core\Content\Product\SalesChannel\Listing\ProductListingResult;
use Shopware\Core\Content\Product\SalesChannel\Listing\ProductListingRouteResponse;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepositoryInterface;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;

interface EntityListingInterface
{
    public function setRequest(Request $request): void;
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher): void;
    public function setSalesChannelContext(SalesChannelContext $salesChannelContext): void;
    public function setEvent(ProductListingResultEvent $event): void;
    public function getSalesChannelRepository(): ?SalesChannelRepositoryInterface;
    public function getLimit(): int;
    public function getEntityName(): string;
    public function getTitle(): string;
    public function processSearchResult(ProductListingResult $searchResult): void;
    public function processCriteria(Criteria $criteria): void;
    public function inheritCriteria(): bool;
    public function isActive(): bool;
    public function getTerm(string $search): string;
    public function getSnippet(): ?string;
    public function getTemplatePath(): ?string;
    public function setSystemConfigService(SystemConfigService $systemConfigService): void;

    public function listingRoute(Criteria $criteria, ?string $categoryId = null): ProductListingRouteResponse;
}
