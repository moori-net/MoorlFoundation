<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer;

use Shopware\Core\Content\Cms\CmsPageEntity;

trait EntityThingPageTrait
{
    protected ?string $cmsPageId = null;
    protected ?CmsPageEntity $cmsPage = null;
    protected ?array $slotConfig = null;

    /**
     * @return string|null
     */
    public function getCmsPageId(): ?string
    {
        return $this->cmsPageId;
    }

    /**
     * @param string|null $cmsPageId
     */
    public function setCmsPageId(?string $cmsPageId): void
    {
        $this->cmsPageId = $cmsPageId;
    }

    /**
     * @return CmsPageEntity|null
     */
    public function getCmsPage(): ?CmsPageEntity
    {
        return $this->cmsPage;
    }

    /**
     * @param CmsPageEntity|null $cmsPage
     */
    public function setCmsPage(?CmsPageEntity $cmsPage): void
    {
        $this->cmsPage = $cmsPage;
    }

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
