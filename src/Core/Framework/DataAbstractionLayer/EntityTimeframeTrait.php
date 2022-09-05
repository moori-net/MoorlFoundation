<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer;

trait EntityTimeframeTrait
{
    protected ?\DateTimeImmutable $showForm = null;
    protected ?\DateTimeImmutable $showUntil = null;

    /**
     * @return \DateTimeImmutable|null
     */
    public function getShowForm(): ?\DateTimeImmutable
    {
        return $this->showForm;
    }

    /**
     * @param \DateTimeImmutable|null $showForm
     */
    public function setShowForm(?\DateTimeImmutable $showForm): void
    {
        $this->showForm = $showForm;
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
