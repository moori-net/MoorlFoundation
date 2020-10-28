<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer\Dbal\FieldAccessorBuilder;

use MoorlFoundation\Core\Framework\DataAbstractionLayer\Field\DistanceField;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Dbal\FieldAccessorBuilder\FieldAccessorBuilderInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Field;

class DistanceFieldAccessorBuilder implements FieldAccessorBuilderInterface
{
    public function buildAccessor(string $root, Field $field, Context $context, string $accessor): ?string
    {
        if (!$field instanceof DistanceField) {
            return null;
        }

        if (!$context->getExtension('DistanceField')) {
            return null;
        }

        $lat = (float) $context->getExtension('DistanceField')['lat'];
        $lon = (float) $context->getExtension('DistanceField')['lon'];

        return sprintf(
            '(ACOS(SIN(RADIANS(%F)) * SIN(RADIANS(`%s`.`%s`)) + COS(RADIANS(%F)) * COS(RADIANS(`%s`.`%s`)) * COS(RADIANS(%F) - RADIANS(`%s`.`%s`))) * 6380)',
            $lat,
            $root,
            $field->getLat(),
            $lat,
            $root,
            $field->getLat(),
            $lon,
            $root,
            $field->getLon()
        );
    }
}
