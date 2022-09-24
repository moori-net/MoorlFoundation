<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer;

trait EntityTimeframeTrait
{
    protected ?\DateTimeImmutable $showFrom = null;
    protected ?\DateTimeImmutable $showUntil = null;

    /**
     * @return \DateTimeImmutable|null
     */
    public function getShowFrom(): ?\DateTimeImmutable
    {
        return $this->showFrom;
    }

    /**
     * @param \DateTimeImmutable|null $showFrom
     */
    public function setShowFrom(?\DateTimeImmutable $showFrom): void
    {
        $this->showFrom = $showFrom;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getShowUntil(): ?\DateTimeImmutable
    {
        return $this->showUntil;
    }

    /**
     * @param \DateTimeImmutable|null $showUntil
     */
    public function setShowUntil(?\DateTimeImmutable $showUntil): void
    {
        $this->showUntil = $showUntil;
    }
}
