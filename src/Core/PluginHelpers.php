<?php

namespace MoorlFoundation\Core;

class PluginHelpers
{
    public static function assignArrayByPath(&$arr, $path, $value, $separator = '.')
    {
        $keys = explode($separator, (string)$path);
        foreach ($keys as $key) {
            $arr = &$arr[$key];
        }
        $arr = $value;
    }

    public static function getNestedVar(&$context)
    {
        foreach ($context as $name => $item) {
            self::assignArrayByPath($context, $name, $item);
        }
    }

    public static function setNestedVar(&$context)
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
}
