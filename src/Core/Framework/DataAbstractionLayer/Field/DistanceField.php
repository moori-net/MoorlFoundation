<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer\Field;

use MoorlFoundation\Core\Framework\DataAbstractionLayer\Dbal\FieldAccessorBuilder\DistanceFieldAccessorBuilder;
use MoorlFoundation\Core\Framework\DataAbstractionLayer\Dbal\FieldResolver\DistanceFieldResolver;
use MoorlFoundation\Core\Framework\DataAbstractionLayer\FieldSerializer\DistanceFieldSerializer;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Field;

class DistanceField extends Field
{
    /**
     * @var string
     */
    protected $lat;
    /**
     * @var string
     */
    protected $lon;

    public function __construct(
        $propertyName = 'distance',
        $lat = 'location_lat',
        $lon = 'location_lon'
    ) {
        $this->lat = $lat;
        $this->lon = $lon;
        parent::__construct($propertyName);
    }

    /**
     * @return string
     */
    public function getLat(): string
    {
        return $this->lat;
    }

    /**
     * @return string
     */
    public function getLon(): string
    {
        return $this->lon;
    }

    protected function getAccessorBuilderClass(): ?string
    {
        return DistanceFieldAccessorBuilder::class;
    }

    protected function getResolverClass(): ?string
    {
        return DistanceFieldResolver::class;
    }

    protected function getSerializerClass(): string
    {
        return DistanceFieldSerializer::class;
    }
}
