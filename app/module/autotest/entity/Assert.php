<?php

namespace Tymy\Module\Autotest\Entity;

use Tymy\Module\Autotest\SimpleResponse;

/**
 * Description of Assert
 */
class Assert extends \Tester\Assert
{
    /**
     * Asserts that tymy system response is in error format and that responded code equals to expected one
     */
    public static function errcode($expected, SimpleResponse $response): void
    {
        $description = print_r($response->getData(), true);
        Assert::notEqual(200, $response->getCode());
        Assert::notEqual(201, $response->getCode());

        self::hasKey("code", $response->getData(), $description);
        self::hasKey("message", $response->getData(), $description);
        self::equal($expected, $response->getData()["code"], $description);
    }
}
