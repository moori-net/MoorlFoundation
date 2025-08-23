<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer\Filter;

use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;

class EntityActiveFilter extends EqualsFilter
{
    public function __construct(string $prefix)
    {
        parent::__construct($prefix . '.active', true);
    }
}
