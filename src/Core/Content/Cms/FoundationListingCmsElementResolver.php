<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\Cms;

use MoorlFoundation\Core\Framework\Plugin\Exception\TypePatternException;
use MoorlFoundation\Core\Service\SortingService;
use Shopware\Core\Content\Cms\Aggregate\CmsSlot\CmsSlotEntity;
use Shopware\Core\Content\Cms\DataResolver\CriteriaCollection;
use Shopware\Core\Content\Cms\DataResolver\Element\AbstractCmsElementResolver;
use Shopware\Core\Content\Cms\DataResolver\Element\ElementDataCollection;
use Shopware\Core\Content\Cms\DataResolver\ResolverContext\EntityResolverContext;
use Shopware\Core\Content\Cms\DataResolver\ResolverContext\ResolverContext;
use Shopware\Core\Content\Cms\Exception\UnexpectedFieldConfigValueType;
use Shopware\Core\Content\Product\SalesChannel\Listing\AbstractProductListingRoute;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\NotFilter;

class FoundationListingCmsElementResolver extends AbstractCmsElementResolver
{
    public function __construct(
        protected readonly SortingService $sortingService,
        protected readonly ?AbstractProductListingRoute $listingRoute = null
    )
    {
    }

    /**
     * @return string
     * @throws TypePatternException
     */
    public function getType(): string
    {
        throw new TypePatternException(self::class);
    }

    public function collect(CmsSlotEntity $slot, ResolverContext $resolverContext): ?CriteriaCollection
    {
        return null;
    }

    public function enrich(CmsSlotEntity $slot, ResolverContext $resolverContext, ElementDataCollection $result): void
    {
        return;
    }

    protected function getNavigationId(ResolverContext $resolverContext): string
    {
        $request = $resolverContext->getRequest();
        $salesChannelContext = $resolverContext->getSalesChannelContext();

        if ($navigationId = $request->request->get('navigationId')) {
            return $navigationId;
        }

        $params = $request->attributes->get('_route_params');

        if ($params && isset($params['navigationId'])) {
            return $params['navigationId'];
        }

        return $salesChannelContext->getSalesChannel()->getNavigationCategoryId();
    }

    /**
     * @throws UnexpectedFieldConfigValueType
     */
    protected function enrichCmsElementResolverCriteriaV2(
        CmsSlotEntity $slot,
        Criteria $criteria,
        ResolverContext $resolverContext
    ): void
    {
        $request = $resolverContext->getRequest();
        $salesChannelContext = $resolverContext->getSalesChannelContext();

        if ($request) {
            /* Unset immediately in EntitySearchService, because it's not compatible with product listing */
            $request->query->set('slots', $slot->getId());
        }

        $criteria->setTitle("cms::" . $slot->getType());

        $config = $slot->getFieldConfig();

        $limitConfig = $config->get('limit');
        if ($limitConfig && $limitConfig->getValue()) {
            $criteria->setLimit($limitConfig->getValue());
            if ($request) {
                /* Unset immediately in EntitySearchService, because it's not compatible with product listing */
                $request->query->set('limit', $limitConfig->getValue());
            }
        }

        $listingSourceConfig = $config->get('listingSource');
        if ($listingSourceConfig && $listingSourceConfig->getValue() === 'select') {
            $listingItemIdsConfig = $config->get('listingItemIds');
            if ($listingItemIdsConfig && $listingItemIdsConfig->getArrayValue()) {
                $criteria->setIds($listingItemIdsConfig->getArrayValue());
            }
        }

        $listingSortingConfig = $config->get('listingSorting');
        if ($listingSortingConfig && $listingSortingConfig->getValue()) {
            $sorting = $this->sortingService->addSortingCriteria(
                $listingSortingConfig->getValue(),
                $criteria,
                $salesChannelContext->getContext()
            );

            if ($listingSourceConfig && $listingSourceConfig->getValue() === 'auto') {
                $criteria->resetSorting();
                if ($request && !$request->query->get('order') && $sorting) {
                    $request->query->set('order', $sorting->getKey());
                }
            }
        }

        if (!$resolverContext instanceof EntityResolverContext) {
            return;
        }

        $translatedConfig = $slot->getTranslated()['config'];

        $foreignKeyConfig = $config->get('foreignKey');
        if ($foreignKeyConfig && $foreignKeyConfig->getValue()) {
            /* Ignore filter if manual selected */
            if (!$listingSourceConfig || $listingSourceConfig->getValue() !== 'select') {
                $criteria->addFilter(new EqualsFilter(
                    $foreignKeyConfig->getValue(),
                    $resolverContext->getEntity()->getUniqueIdentifier()
                ));
            }
        } else {
            /* Exclude self id - show only `other` entities */
            $criteria->addFilter(new NotFilter(NotFilter::CONNECTION_AND, [
                new EqualsFilter('id', $resolverContext->getEntity()->getUniqueIdentifier())
            ]));
        }

        /* Handle placeholders */
        if (!empty($translatedConfig['listingHeaderTitle']['value'])) {
            $translatedConfig['listingHeaderTitle']['value'] = $this->resolveEntityValues(
                $resolverContext,
                $translatedConfig['listingHeaderTitle']['value']
            );

            $slot->addTranslated('config', $translatedConfig);
        }

        if (!empty($translatedConfig['buttonLabel']['value'])) {
            $translatedConfig['buttonLabel']['value'] = $this->resolveEntityValues(
                $resolverContext,
                $translatedConfig['buttonLabel']['value']
            );

            $slot->addTranslated('config', $translatedConfig);
        }
    }
}
