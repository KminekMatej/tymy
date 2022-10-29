<?php

namespace Tymy\Module\Core\Helper;

use DateTimeZone;
use Exception;
use Nette\Utils\DateTime;

class DateHelper
{
    /**
     * Create DateTime object from various formats
     * @param mixed $datetime
     * @param DateTimeZone|null $timezone
     * @return \DateTime
     */
    public static function create(mixed $datetime = "now", ?DateTimeZone $timezone = null): \DateTime
    {
        if (is_a($datetime, \DateTime::class)) { //if input is actually a DateTime class, create this DateTime from it
            return $datetime;
        } elseif ($atomDate = DateTime::createFromFormat(DATE_ATOM, $datetime, $timezone)) { //mainly - attempt to create DateTime from ATOM format, which is the only one valid for ISO 8601
            return $atomDate;
        } else {
            return new DateTime($datetime, $timezone); //in every other occasion, use native constructor
        }
    }

    /**
     * Create DateTime object from various formats and setis it to current datetimezone
     * @param mixed $datetime
     * @param DateTimeZone|null $timezone
     * @return \DateTime
     */
    public static function createLc(mixed $datetime = "now", ?DateTimeZone $timezone = null): \DateTime
    {
        return self::toLocal(self::create($datetime, $timezone));
    }

    /**
     * Sets local timzone to DateTime object
     * @param \DateTime $datetime
     * @return void
     */
    public static function toLocal(\DateTime $datetime): \DateTime
    {
        return $datetime->setTimezone(new DateTimeZone(date_default_timezone_get()));
    }

    public static function validateDate($date)
    {
        $date = trim($date);
        try {
            $date = ($date) ? new DateTime($date) : null;
        } catch (Exception $e) {
            $date = null;
        }
        return $date;
    }
}
