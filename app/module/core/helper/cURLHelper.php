<?php

// phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps

namespace Tymy\Module\Core\Helper;

class CURLHelper
{
    /**
     * Get content from url
     *
     * @return string|array If its json data
     */
    public static function get(string $url, bool $isJson = false): string|array
    {
        $handle = self::getCurl($url);
        $response = curl_exec($handle);
        curl_close($handle);

        if ($isJson) {
            $response = json_decode($response, true, 512, JSON_THROW_ON_ERROR);
        }

        return $response;
    }

    /**
     * Initialize curl handle with common settings
     */
    private static function getCurl(string $url): \CurlHandle|bool
    {
        $handle = curl_init($url);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT'] ?? "Tymy.cz php application");
        curl_setopt($handle, CURLOPT_REFERER, $_SERVER['HTTP_REFERER'] ?? "localhost");
        return $handle;
    }
}
