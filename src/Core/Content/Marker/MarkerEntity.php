<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\Marker;

use Shopware\Core\Content\Media\MediaEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class MarkerEntity extends Entity
{
    use EntityIdTrait;

    protected ?string $markerId = null;
    protected ?MediaEntity $marker = null;
    protected ?string $markerRetinaId = null;
    protected ?MediaEntity $markerRetina = null;
    protected ?string $markerShadowId = null;
    protected ?MediaEntity $markerShadow = null;
    protected ?array $markerSettings = null;
    protected ?string $type = null;
    protected ?string $name = null;
    protected ?string $className = null;
    protected ?string $svg = null;

    public function getLeafletMarker(): array
    {
        $ms = $this->markerSettings ?: [
            "iconSizeX" => 38,
            "iconSizeY" => 95,
            "iconAnchorX" => 22,
            "iconAnchorY" => 94,
            "shadowSizeX" => 50,
            "shadowSizeY" => 64,
            "popupAnchorX" => -3,
            "popupAnchorY" => -76,
            "shadowAnchorX" => 4,
            "shadowAnchorY" => 62
        ];

        return [
            'svg' => $this->svg,
            'className' => $this->className,
            'iconUrl' => $this->marker ? $this->marker->getUrl() : null,
            'iconRetinaUrl' => $this->markerRetina ? $this->markerRetina->getUrl() : null,
            'shadowUrl' => $this->markerShadow ? $this->markerShadow->getUrl() : null,
            'iconSize' => [$ms['iconSizeX'], $ms['iconSizeY']],
            'iconAnchor' => [$ms['iconAnchorX'], $ms['iconAnchorY']],
            'popupAnchor' => [$ms['popupAnchorX'], $ms['popupAnchorY']],
            'shadowSize' => [$ms['shadowSizeX'], $ms['shadowSizeY']],
            'shadowAnchor' => [$ms['shadowAnchorX'], $ms['shadowAnchorY']],
        ];
    }

    public function getClassName(): ?string
    {
        return $this->className;
    }

    public function setClassName(?string $className): void
    {
        $this->className = $className;
    }

    public function getSvg(): ?string
    {
        return $this->svg;
    }

    public function setSvg(?string $svg): void
    {
        $this->svg = $svg;
    }

    public function getMarkerId(): ?string
    {
        return $this->markerId;
    }

    public function setMarkerId(?string $markerId): void
    {
        $this->markerId = $markerId;
    }

    public function getMarker(): ?MediaEntity
    {
        return $this->marker;
    }

    public function setMarker(?MediaEntity $marker): void
    {
        $this->marker = $marker;
    }

    public function getMarkerRetinaId(): ?string
    {
        return $this->markerRetinaId;
    }

    public function setMarkerRetinaId(?string $markerRetinaId): void
    {
        $this->markerRetinaId = $markerRetinaId;
    }

    public function getMarkerRetina(): ?MediaEntity
    {
        return $this->markerRetina;
    }

    public function setMarkerRetina(?MediaEntity $markerRetina): void
    {
        $this->markerRetina = $markerRetina;
    }

    public function getMarkerShadowId(): ?string
    {
        return $this->markerShadowId;
    }

    public function setMarkerShadowId(?string $markerShadowId): void
    {
        $this->markerShadowId = $markerShadowId;
    }

    public function getMarkerShadow(): ?MediaEntity
    {
        return $this->markerShadow;
    }

    public function setMarkerShadow(?MediaEntity $markerShadow): void
    {
        $this->markerShadow = $markerShadow;
    }

    public function getMarkerSettings(): ?array
    {
        return $this->markerSettings;
    }

    public function setMarkerSettings(?array $markerSettings): void
    {
        $this->markerSettings = $markerSettings;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): void
    {
        $this->type = $type;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }
}
