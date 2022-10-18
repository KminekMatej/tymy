<?php

namespace Tymy\Module\Autotest\Entity;

use Nette\Utils\DateTime;
use Tymy\Module\Autotest\SimpleResponse;
use Tymy\Module\Core\Model\BaseModel;

/**
 * Description of Assert
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 16. 1. 2020
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

    public static function datetimeEquals(string $expected, string $actual): void
    {
        $newDateTime = DateTime::createFromFormat(BaseModel::DATE_FORMAT, $actual, "UTC");
        $oldDateTime = DateTime::createFromFormat(BaseModel::DATE_FORMAT, $expected);

        Assert::true($oldDateTime === $newDateTime, "Error on datetime field, $expected !== $actual"); //comparing by equal fails due to different timezones
    }
}
