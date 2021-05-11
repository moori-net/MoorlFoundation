<?php

namespace MoorlFoundation\Core;

class PluginHelpers
{
    public static function scrambleWord(string $word): string
    {
        if (strlen($word) < 2)
            return $word;
        else
            return $word[0] . str_shuffle(substr($word, 1, -1)) . $word[strlen($word) - 1];
    }

    public static function scrambleText(string $text): string
    {
        return preg_replace('/(\w+)/e', 'self::scrambleWord("\1")', $text);
    }

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
