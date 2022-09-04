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

    /**
     * @return string|null
     */
    public function getMarkerId(): ?string
    {
        return $this->markerId;
    }

    /**
     * @param string|null $markerId
     */
    public function setMarkerId(?string $markerId): void
    {
        $this->markerId = $markerId;
    }

    /**
     * @return MediaEntity|null
     */
    public function getMarker(): ?MediaEntity
    {
        return $this->marker;
    }

    /**
     * @param MediaEntity|null $marker
     */
    public function setMarker(?MediaEntity $marker): void
    {
        $this->marker = $marker;
    }

    /**
     * @return string|null
     */
    public function getMarkerRetinaId(): ?string
    {
        return $this->markerRetinaId;
    }

    /**
     * @param string|null $markerRetinaId
     */
    public function setMarkerRetinaId(?string $markerRetinaId): void
    {
        $this->markerRetinaId = $markerRetinaId;
    }

    /**
     * @return MediaEntity|null
     */
    public function getMarkerRetina(): ?MediaEntity
    {
        return $this->markerRetina;
    }

    /**
     * @param MediaEntity|null $markerRetina
     */
    public function setMarkerRetina(?MediaEntity $markerRetina): void
    {
        $this->markerRetina = $markerRetina;
    }

    /**
     * @return string|null
     */
    public function getMarkerShadowId(): ?string
    {
        return $this->markerShadowId;
    }

    /**
     * @param string|null $markerShadowId
     */
    public function setMarkerShadowId(?string $markerShadowId): void
    {
        $this->markerShadowId = $markerShadowId;
    }

    /**
     * @return MediaEntity|null
     */
    public function getMarkerShadow(): ?MediaEntity
    {
        return $this->markerShadow;
    }

    /**
     * @param MediaEntity|null $markerShadow
     */
    public function setMarkerShadow(?MediaEntity $markerShadow): void
    {
        $this->markerShadow = $markerShadow;
    }

    /**
     * @return array|null
     */
    public function getMarkerSettings(): ?array
    {
        return $this->markerSettings;
    }

    /**
     * @param array|null $markerSettings
     */
    public function setMarkerSettings(?array $markerSettings): void
    {
        $this->markerSettings = $markerSettings;
    }

    /**
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @param string|null $type
     */
    public function setType(?string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     */
    public function setName(?string $name): void
    {
        $this->name = $name;
    }
}
