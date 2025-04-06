<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer;

trait EntityThingPageTranslationTrait
{
    protected ?array $slotConfig = null;

    public function getSlotConfig(): ?array
    {
        return $this->slotConfig;
    }

    public function setSlotConfig(?array $slotConfig): void
    {
        $this->slotConfig = $slotConfig;
    }
}
