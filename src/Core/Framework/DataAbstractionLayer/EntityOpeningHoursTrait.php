<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer;

trait EntityOpeningHoursTrait
{
    protected array $openingHours = [];
    protected string $timeZone = 'Europe/Berlin';

    /**
     * @return string
     */
    public function getTimeZone(): string
    {
        return $this->timeZone;
    }

    /**
     * @param string $timeZone
     */
    public function setTimeZone(string $timeZone): void
    {
        $this->timeZone = $timeZone;
    }

    /**
     * @return array
     */
    public function getOpeningHours(): array
    {
        return $this->openingHours;
    }

    /**
     * @param array $openingHours
     */
    public function setOpeningHours(array $openingHours): void
    {
        $this->openingHours = $openingHours;
    }
}
