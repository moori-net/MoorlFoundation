<?php declare(strict_types=1);

namespace MoorlFoundation\Core\System;

use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepositoryInterface;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;

interface SalesChannelEntitySearchInterface
{
    public function getSalesChannelRepository(): ?SalesChannelRepositoryInterface;
    public function getLimit(): int;
    public function getEntityName(): string;
    public function getTitle(): string;
    public function getEntityDefinition(): EntityDefinition;
    public function setEntityDefinition(EntityDefinition $entityDefinition): void;
    public function processSearchResult(EntitySearchResult $searchResult): void;
    public function processCriteria(Criteria $criteria): void;
    public function inheritCriteria(): bool;
    public function isActive(): bool;
    public function getTerm(string $search): string;
    public function getSnippet(): ?string;
    public function getTemplatePath(): ?string;
    public function getSystemConfigService(): SystemConfigService;
    public function setSystemConfigService(SystemConfigService $systemConfigService): void;
    public function setSalesChannelContext(SalesChannelContext $salesChannelContext): void;
}
