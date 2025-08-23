<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer;

trait EntityCustomTrait
{
    protected ?string $custom1 = null;
    protected ?string $custom2 = null;
    protected ?string $custom3 = null;
    protected ?string $custom4 = null;

    public function getCustom1(): ?string
    {
        return $this->custom1;
    }

    public function setCustom1(?string $custom1): void
    {
        $this->custom1 = $custom1;
    }

    public function getCustom2(): ?string
    {
        return $this->custom2;
    }

    public function setCustom2(?string $custom2): void
    {
        $this->custom2 = $custom2;
    }

    public function getCustom3(): ?string
    {
        return $this->custom3;
    }

    public function setCustom3(?string $custom3): void
    {
        $this->custom3 = $custom3;
    }

    public function getCustom4(): ?string
    {
        return $this->custom4;
    }

    public function setCustom4(?string $custom4): void
    {
        $this->custom4 = $custom4;
    }
}
