<?php

namespace Tymy\Module\Core\Helper;

/**
 * Description of StringHelper
 *
 * @author kminekmatej, 4. 5. 2022, 21:59:59
 */
class StringHelper
{
    /**
     * Encode url, along with dots and hyphens
     * @param string $input
     * @return string
     */
    public static function urlencode(string $input): string
    {
        $str = urlencode($input);
        $str = str_replace('.', '%2E', $str);
        $str = str_replace('-', '%2D', $str);
        return $str;
    }

    /**
     * Decode url, along with dots and hyphens
     * @param string $input
     * @return string
     */
    public static function urldecode(string $input): string
    {
        $str = urldecode($input);
        $str = str_replace('%2E', '.', $str);
        $str = str_replace('%2D', '-', $str);
        return $str;
    }
}
