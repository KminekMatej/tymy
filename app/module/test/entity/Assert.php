<?php

namespace Tymy\Test\Entity;

use Tymy\Test\SimpleResponse;

/**
 * Description of Assert
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 16. 1. 2020
 */
class Assert extends \Tester\Assert
{
    /**
     * Asserts that a haystack (string or array) has an expected key.
     */
    public static function hasKey($key, $actual, string $description = null): void
    {
        self::$counter++;
        if (is_array($actual)) {
            if (!array_key_exists($key, $actual)) {
                self::fail(self::describe('%1 should contain key %2', $description), $actual, $key);
            }
        } else {
            self::fail(self::describe('%1 should be array', $description), $actual);
        }
    }

    /**
     * Asserts that a haystack (string or array) has not an expected key.
     */
    public static function hasNotKey($key, $actual, string $description = null): void
    {
        self::$counter++;
        if (is_array($actual)) {
            if (array_key_exists($key, $actual)) {
                self::fail(self::describe('%1 should not contain key %2', $description), $actual, $key);
            }
        } else {
            self::fail(self::describe('%1 should be array', $description), $actual);
        }
    }

    private static function describe(string $reason, string $description = null): string
    {
        return ($description ? $description . ': ' : '') . $reason;
    }

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
