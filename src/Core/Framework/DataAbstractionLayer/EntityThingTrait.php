<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer;

use Shopware\Core\Content\Cms\CmsPageEntity;
use Shopware\Core\Content\Media\MediaEntity;

trait EntityThingTrait
{
    protected bool $visible = false;
    protected bool $active = false;
    protected string $name = "";
    protected ?string $teaser = null;
    protected ?string $keywords = null;
    protected ?string $description = null;
    protected ?string $metaTitle = null;
    protected ?string $metaDescription = null;
    protected ?string $cmsPageId = null;
    protected ?CmsPageEntity $cmsPage = null;
    protected ?string $mediaId = null;
    protected ?MediaEntity $media = null;
    protected ?array $slotConfig = null;

    /**
     * @return string|null
     */
    public function getKeywords(): ?string
    {
        return $this->keywords;
    }

    /**
     * @param string|null $keywords
     */
    public function setKeywords(?string $keywords): void
    {
        $this->keywords = $keywords;
    }

    /**
     * @return bool
     */
    public function getVisible(): bool
    {
        return $this->visible;
    }

    /**
     * @param bool $visible
     */
    public function setVisible(bool $visible): void
    {
        $this->visible = $visible;
    }

    /**
     * @return string|null
     */
    public function getMediaId(): ?string
    {
        return $this->mediaId;
    }

    /**
     * @param string|null $mediaId
     */
    public function setMediaId(?string $mediaId): void
    {
        $this->mediaId = $mediaId;
    }

    /**
     * @return MediaEntity|null
     */
    public function getMedia(): ?MediaEntity
    {
        return $this->media;
    }

    /**
     * @param MediaEntity|null $media
     */
    public function setMedia(?MediaEntity $media): void
    {
        $this->media = $media;
    }

    /**
     * @return bool
     */
    public function getActive(): bool
    {
        return $this->active;
    }

    /**
     * @param bool $active
     */
    public function setActive(bool $active): void
    {
        $this->active = $active;
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

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string|null
     */
    public function getTeaser(): ?string
    {
        return $this->teaser;
    }

    /**
     * @param string|null $teaser
     */
    public function setTeaser(?string $teaser): void
    {
        $this->teaser = $teaser;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     */
    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return string|null
     */
    public function getMetaTitle(): ?string
    {
        return $this->metaTitle;
    }

    /**
     * @param string|null $metaTitle
     */
    public function setMetaTitle(?string $metaTitle): void
    {
        $this->metaTitle = $metaTitle;
    }

    /**
     * @return string|null
     */
    public function getMetaDescription(): ?string
    {
        return $this->metaDescription;
    }

    /**
     * @param string|null $metaDescription
     */
    public function setMetaDescription(?string $metaDescription): void
    {
        $this->metaDescription = $metaDescription;
    }

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
}
