<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer;

trait EntityOpeningHoursTrait
{
    protected array $openingHours = [];
    protected string $timeZone = 'Europe/Berlin';

    public function getTimeZone(): string
    {
        return $this->timeZone;
    }

    public function setTimeZone(string $timeZone): void
    {
        $this->timeZone = $timeZone;
    }

    public function getOpeningHours(): array
    {
        return $this->openingHours;
    }

    public function setOpeningHours(array $openingHours): void
    {
        $this->openingHours = $openingHours;
    }
}
