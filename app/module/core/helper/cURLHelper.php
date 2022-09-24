<?php

// phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps

namespace Tymy\Module\Core\Helper;

class CURLHelper
{
    /**
     * Get content from url
     * @param string $url
     * @param bool $isJson
     * @return string|array If its json data
     */
    public static function get(string $url, bool $isJson = false)
    {
        $handle = self::getCurl($url);
        $response = curl_exec($handle);
        $info = curl_getinfo($handle);
        curl_close($handle);

        if ($isJson) {
            $response = json_decode($response, true);
        }

        return $response;
    }

    /**
     * Send POST data via cURL
     *
     * @param string $module
     * @param int $entityId
     * @return CustomFieldValue[]
     */
    public static function sendPost($url, $data, $isJson = false)
    {
        $handle = self::getCurl($url);
        curl_setopt($handle, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($handle, CURLOPT_FAILONERROR, true);
        curl_setopt($handle, CURLOPT_POSTFIELDS, $data);
        $response = curl_exec($handle);
        curl_close($handle);

        if ($isJson) {
            $response = json_decode($response, true);
        }
        return $response;
    }

    /**
     * Initialize curl handle with common settings
     * @param string $url
     * @return \CurlHandle
     */
    private static function getCurl(string $url)
    {
        $handle = curl_init($url);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT'] ?? "Tymy.cz php application");
        curl_setopt($handle, CURLOPT_REFERER, $_SERVER['HTTP_REFERER'] ?? "localhost");
        return $handle;
    }
}
