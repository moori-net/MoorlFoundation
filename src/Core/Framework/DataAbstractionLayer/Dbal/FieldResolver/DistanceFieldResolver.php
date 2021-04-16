<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer\Dbal\FieldResolver;

use MoorlFoundation\Core\Framework\DataAbstractionLayer\Field\DistanceField;
use Shopware\Core\Framework\DataAbstractionLayer\Dbal\FieldResolver\AbstractFieldResolver;
use Shopware\Core\Framework\DataAbstractionLayer\Dbal\FieldResolver\FieldResolverContext;

class DistanceFieldResolver extends AbstractFieldResolver
{
    public function join(FieldResolverContext $context): string
    {
        $field = $context->getField();
        if (!$field instanceof DistanceField) {
            return $context->getAlias();
        }
    }
}
