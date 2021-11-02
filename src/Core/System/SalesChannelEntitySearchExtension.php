<?php declare(strict_types=1);

namespace MoorlFoundation\Core\System;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\Filter;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepositoryInterface;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class SalesChannelEntitySearchExtension
{
    protected EntityDefinition $entityDefinition;
    protected SystemConfigService $systemConfigService;
    protected SalesChannelContext $salesChannelContext;
    protected Context $context;
    protected ?SalesChannelRepositoryInterface $salesChannelRepository = null;
    protected Filter $filter;

    /**
     * @return SalesChannelRepositoryInterface|null
     */
    public function getSalesChannelRepository(): ?SalesChannelRepositoryInterface
    {
        return $this->salesChannelRepository;
    }

    /**
     * @param SalesChannelRepositoryInterface|null $salesChannelRepository
     */
    public function setSalesChannelRepository(?SalesChannelRepositoryInterface $salesChannelRepository): void
    {
        $this->salesChannelRepository = $salesChannelRepository;
    }

    /**
     * @return SalesChannelContext
     */
    public function getSalesChannelContext(): SalesChannelContext
    {
        return $this->salesChannelContext;
    }

    /**
     * @param SalesChannelContext $salesChannelContext
     */
    public function setSalesChannelContext(SalesChannelContext $salesChannelContext): void
    {
        $this->salesChannelContext = $salesChannelContext;
        $this->context = $salesChannelContext->getContext();
    }

    /**
     * @return Context
     */
    public function getContext(): Context
    {
        return $this->context;
    }

    /**
     * @return SystemConfigService
     */
    public function getSystemConfigService(): SystemConfigService
    {
        return $this->systemConfigService;
    }

    /**
     * @param SystemConfigService $systemConfigService
     */
    public function setSystemConfigService(SystemConfigService $systemConfigService): void
    {
        $this->systemConfigService = $systemConfigService;
    }

    public function processSearchResult(EntitySearchResult $searchResult): void
    {
    }

    public function processCriteria(Criteria $criteria): void
    {
    }

    public function isActive(): bool
    {
        return true;
    }

    public function inheritCriteria(): bool
    {
        return false;
    }

    public function getLimit(): int
    {
        return 6;
    }

    public function getTerm(string $search): string
    {
        return $search;
    }

    public function getSnippet(): ?string
    {
        return null;
    }

    public function getTemplatePath(): ?string
    {
        return null;
    }

    /**
     * @return EntityDefinition
     */
    public function getEntityDefinition(): EntityDefinition
    {
        return $this->entityDefinition;
    }

    /**
     * @param EntityDefinition $entityDefinition
     */
    public function setEntityDefinition(EntityDefinition $entityDefinition): void
    {
        $this->entityDefinition = $entityDefinition;
    }
}
