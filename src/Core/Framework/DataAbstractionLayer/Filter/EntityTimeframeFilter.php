<?php declare(strict_types=1);

namespace MoorlFoundation\Core\Framework\DataAbstractionLayer\Filter;

use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\RangeFilter;

class EntityTimeframeFilter extends MultiFilter
{
    public const DATE_FORMAT = 'date_format';
    public const SHOW_BEFORE = 'show_before';
    public const SHOW_AFTER = 'show_after';
    public const SHOW_FROM = 'show_from';
    public const SHOW_UNTIL = 'show_until';
    public const TIMEZONE = 'timezone';

    public function __construct(string $prefix, array $config = [])
    {
        $time = new \DateTime();
        $time = $time->setTimezone(new \DateTimeZone($config[self::TIMEZONE] ?? 'UTC'));
        $format = $config[self::DATE_FORMAT] ?? 'Y-m-d H:i:s';
        $showFrom = isset($config[self::SHOW_BEFORE]) ? $time->modify('+' . $config[self::SHOW_BEFORE] . ' hours') : $time;
        $showUntil = isset($config[self::SHOW_AFTER]) ? $time->modify('-' . $config[self::SHOW_AFTER] . ' hours') : $time;
        $showFromField = $prefix . '.' . ($config[self::SHOW_FROM] ?? 'showFrom');
        $showUntilField = $prefix . '.' . ($config[self::SHOW_UNTIL] ?? 'showUntil');

        parent::__construct(
            self::CONNECTION_AND,
            [
                new MultiFilter(
                    MultiFilter::CONNECTION_OR, [
                        new EqualsFilter($showFromField, null),
                        new RangeFilter($showFromField, [
                            RangeFilter::LTE => $showFrom->format($format)
                        ])
                    ]
                ),
                new MultiFilter(
                    MultiFilter::CONNECTION_OR, [
                        new EqualsFilter($showUntilField, null),
                        new RangeFilter($showUntilField, [
                            RangeFilter::GTE => $showUntil->format($format)
                        ])
                    ]
                ),
            ]
        );
    }
}
