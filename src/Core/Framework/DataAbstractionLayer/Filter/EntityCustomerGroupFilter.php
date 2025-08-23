<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer\Filter;

use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;

class EntityCustomerGroupFilter extends MultiFilter
{
    public function __construct(string $prefix, ?string $id = null)
    {
        if (!$id) {
            parent::__construct(self::CONNECTION_OR);
        } else {
            parent::__construct(
                self::CONNECTION_OR,
                [
                    new EqualsFilter($prefix . '.customerGroupId', null),
                    new EqualsFilter($prefix . '.customerGroupId', $id)
                ]
            );
        }
    }
}
