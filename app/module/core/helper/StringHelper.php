<?php

namespace Tymy\Module\Core\Helper;

/**
 * Description of StringHelper
 */
class StringHelper
{
    /**
     * Encode url, along with dots and hyphens
     */
    public static function urlencode(string $input): string
    {
        $str = urlencode($input);
        $str = str_replace('.', '%2E', $str);
        return str_replace('-', '%2D', $str);
    }

    /**
     * Decode url, along with dots and hyphens
     */
    public static function urldecode(string $input): string
    {
        $str = urldecode($input);
        $str = str_replace('%2E', '.', $str);
        return str_replace('%2D', '-', $str);
    }

    /**
     * Convert string written in dashes and underscores to camelCase
     *
     * @param string $string
     * @param bool $capitalizeFirstCharacter
     * @return string
     */
    public static function toCamelCase(string $string, bool $capitalizeFirstCharacter = false): string
    {
        $str = str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $string)));

        if (!$capitalizeFirstCharacter) {
            $str[0] = strtolower($str[0]);
        }

        return $str;
    }
}
