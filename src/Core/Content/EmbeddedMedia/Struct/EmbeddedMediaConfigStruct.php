<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\EmbeddedMedia\Struct;

use Shopware\Core\Framework\Struct\Struct;

class EmbeddedMediaConfigStruct extends Struct
{
    protected bool $cookieConsent = true;
    protected bool $disablePointerEvents = true;
    protected bool $autoPlay = true;
    protected bool $autoPause = true;
    protected array $video = ['autoplay', 'muted', 'controls'];

    public function getCookieConsent(): bool
    {
        return $this->cookieConsent;
    }

    public function setCookieConsent(bool $cookieConsent): void
    {
        $this->cookieConsent = $cookieConsent;
    }

    public function getDisablePointerEvents(): bool
    {
        return $this->disablePointerEvents;
    }

    public function setDisablePointerEvents(bool $disablePointerEvents): void
    {
        $this->disablePointerEvents = $disablePointerEvents;
    }

    public function getAutoPlay(): bool
    {
        return $this->autoPlay;
    }

    public function setAutoPlay(bool $autoPlay): void
    {
        $this->autoPlay = $autoPlay;
    }

    public function getAutoPause(): bool
    {
        return $this->autoPause;
    }

    public function setAutoPause(bool $autoPause): void
    {
        $this->autoPause = $autoPause;
    }

    public function getVideo(): array
    {
        return $this->video;
    }

    public function setVideo(array $video): void
    {
        $this->video = $video;
    }

    public function getApiAlias(): string
    {
        return 'moorl_media_config';
    }
}
