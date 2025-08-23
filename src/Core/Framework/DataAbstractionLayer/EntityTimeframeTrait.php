<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer;

trait EntityTimeframeTrait
{
    protected ?\DateTimeImmutable $showFrom = null;
    protected ?\DateTimeImmutable $showUntil = null;

    public function getShowFrom(): ?\DateTimeImmutable
    {
        return $this->showFrom;
    }

    public function setShowFrom(?\DateTimeImmutable $showFrom): void
    {
        $this->showFrom = $showFrom;
    }

    public function getShowUntil(): ?\DateTimeImmutable
    {
        return $this->showUntil;
    }

    public function setShowUntil(?\DateTimeImmutable $showUntil): void
    {
        $this->showUntil = $showUntil;
    }
}
