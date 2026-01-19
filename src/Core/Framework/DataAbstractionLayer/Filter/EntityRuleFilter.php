<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer\Filter;

use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class EntityRuleFilter extends MultiFilter
{
    public function __construct(string $prefix, ?SalesChannelContext $salesChannelContext = null)
    {
        if (!$salesChannelContext) {
            parent::__construct(self::CONNECTION_OR);
        } else {
            $ruleIds = $salesChannelContext->getRuleIds();

            parent::__construct(
                self::CONNECTION_OR,
                [
                    new EqualsFilter($prefix . '.ruleId', null),
                    new EqualsAnyFilter($prefix . '.ruleId', $ruleIds)
                ]
            );
        }
    }
}
