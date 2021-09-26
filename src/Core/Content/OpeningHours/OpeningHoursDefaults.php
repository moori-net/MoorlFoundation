<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Content\OpeningHours;

final class OpeningHoursDefaults
{
    public static function getOpeningHours(array $excludeDays = []): array
    {
        $days = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];
        $data = [];

        foreach ($days as $day) {
            $data[] = [
                'day' => $day,
                'info' => null,
                'times' => in_array($day, $excludeDays) ? [] : self::getTimes()
            ];
        }

        return $data;
    }

    public static function getTimes(): array
    {
        return [
            ['from' => '09:00', 'until' => '12:00'],
            ['from' => '13:30', 'until' => '22:00']
        ];
    }
}
