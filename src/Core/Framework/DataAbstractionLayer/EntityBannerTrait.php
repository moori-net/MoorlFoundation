<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer;

use Shopware\Core\Content\Media\MediaEntity;

trait EntityBannerTrait
{
    protected ?string $bannerId = null;
    protected ?string $bannerColor = null;
    protected ?MediaEntity $banner = null;

    public function getBannerId(): ?string
    {
        return $this->bannerId;
    }

    public function setBannerId(?string $bannerId): void
    {
        $this->bannerId = $bannerId;
    }

    public function getBannerColor(): ?string
    {
        return $this->bannerColor;
    }

    public function setBannerColor(?string $bannerColor): void
    {
        $this->bannerColor = $bannerColor;
    }

    public function getBanner(): ?MediaEntity
    {
        return $this->banner;
    }

    public function setBanner(?MediaEntity $banner): void
    {
        $this->banner = $banner;
    }
}
