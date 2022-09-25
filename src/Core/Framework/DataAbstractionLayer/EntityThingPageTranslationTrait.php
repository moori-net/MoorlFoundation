<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer;

use Shopware\Core\Content\Cms\CmsPageEntity;

trait EntityThingPageTranslationTrait
{
    protected ?array $slotConfig = null;

    /**
     * @return array|null
     */
    public function getSlotConfig(): ?array
    {
        return $this->slotConfig;
    }

    /**
     * @param array|null $slotConfig
     */
    public function setSlotConfig(?array $slotConfig): void
    {
        $this->slotConfig = $slotConfig;
    }
}
