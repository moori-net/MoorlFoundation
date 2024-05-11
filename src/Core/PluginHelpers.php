<?php

namespace MoorlFoundation\Core;

class PluginHelpers
{
    public static function assignArrayByPath(&$arr, $path, $value, $separator = '.'): void
    {
        $keys = explode($separator, (string)$path);
        foreach ($keys as $key) {
            $arr = &$arr[$key];
        }
        $arr = $value;
    }

    public static function getNestedVar(&$context): void
    {
        foreach ($context as $name => $item) {
            self::assignArrayByPath($context, $name, $item);
        }
    }

    public static function setNestedVar(&$context): void
    {
        $ritit = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($context));
        foreach ($ritit as $leafValue) {
            $keys = [];
            foreach (range(0, $ritit->getDepth()) as $depth) {
                $keys[] = $ritit->getSubIterator($depth)->key();
            }
            $context[join('.', $keys)] = $leafValue;
        }
    }

    public static function currentTimeFormatted(string $timezone = 'UTC'): string
    {
        $time = new \DateTime();
        return $time->setTimezone(new \DateTimeZone($timezone))->format('Y-m-d H:i:s');
    }
}
