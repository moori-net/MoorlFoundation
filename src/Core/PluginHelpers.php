<?php

namespace MoorlFoundation\Core;

class PluginHelpers
{
    public static function assignArrayByPath(&$arr, $path, $value, $separator='.') {
        $keys = explode($separator, $path);
        foreach ($keys as $key) {
            $arr = &$arr[$key];
        }
        $arr = $value;
    }

    public static function getNestedVar(&$context) {
        foreach ($context as $name => $item) {
            self::assignArrayByPath($context, $name, $item);
        }
    }
}
