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
    public const TIMEZONE = 'timezone';

    public function __construct(string $prefix, array $config = [])
    {
        $time = new \DateTime();

        if (isset($config[self::DATE_FORMAT])) {
            $format = $config[self::DATE_FORMAT];
        } else {
            $format = 'Y-m-d H:i:s';
        }

        if (isset($config[self::TIMEZONE])) {
            $time = $time->setTimezone(new \DateTimeZone($config[self::TIMEZONE]));
        } else {
            $time = $time->setTimezone(new \DateTimeZone('UTC'));
        }

        if (isset($config[self::SHOW_BEFORE])) {
            $showFrom = $time->modify('+' . $config[self::SHOW_BEFORE] . ' hours');
        } else {
            $showFrom = $time;
        }

        if (isset($config[self::SHOW_AFTER])) {
            $showUntil = $time->modify('-' . $config[self::SHOW_AFTER] . ' hours');
        } else {
            $showUntil = $time;
        }

        parent::__construct(
            self::CONNECTION_AND,
            [
                new MultiFilter(
                    MultiFilter::CONNECTION_OR, [
                        new EqualsFilter($prefix . '.showFrom', null),
                        new RangeFilter($prefix . '.showFrom', [
                            RangeFilter::LTE => $showFrom->format($format)
                        ])
                    ]
                ),
                new MultiFilter(
                    MultiFilter::CONNECTION_OR, [
                        new EqualsFilter($prefix . '.showUntil', null),
                        new RangeFilter($prefix . '.showUntil', [
                            RangeFilter::GTE => $showUntil->format($format)
                        ])
                    ]
                ),
            ]
        );
    }
}
