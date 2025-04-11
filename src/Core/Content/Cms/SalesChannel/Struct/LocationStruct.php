<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\Cms\SalesChannel\Struct;

use MoorlFoundation\Core\Content\Marker\MarkerCollection;
use MoorlFoundation\Core\Framework\DataAbstractionLayer\EntityLocationTrait;
use Shopware\Core\Framework\Struct\Struct;

class LocationStruct extends Struct
{
    use EntityLocationTrait;

    protected string $height = "";
    protected bool $legend = false;
    protected bool $overrideOsmOptions = false;
    protected array $legendItems = [];
    protected ?MarkerCollection $markers = null;
    protected array $osmOptions = [];

    public function setMarkers(?MarkerCollection $markers): void
    {
        $this->markers = $markers;
    }

    public function getLegendItems(): array
    {
        return $this->legendItems;
    }

    public function getMarkers(): ?MarkerCollection
    {
        return $this->markers;
    }

    public function getHeight(): string
    {
        return $this->height;
    }

    public function getLegend(): bool
    {
        return $this->legend;
    }

    public function getOsmOptions(): array
    {
        return $this->osmOptions;
    }

    public function isOverrideOsmOptions(): bool
    {
        return $this->overrideOsmOptions;
    }

    public function __set($name, $value): void
    {
        $this->$name = $value;
    }

    public function getApiAlias(): string
    {
        return 'cms_moorl_location';
    }
}
