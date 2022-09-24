<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer;

use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

/**
 * @deprecated: Will be removed
 */
trait FieldCollectionMergeTrait
{
    public static function merge(FieldCollection $collection): void
    {
        foreach ((new self())->__construct() as $field) {
            $collection->add($field);
        }
    }
}
