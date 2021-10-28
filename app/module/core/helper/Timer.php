<?php

namespace Tymy\Module\Core\Helper;

use Nette\Utils\Strings;
use Tracy\Debugger;

/**
 * Description of Timer
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 26. 2. 2020
 */
class Timer
{
    private static $number;
    private static $sumNumber;
    private static $points;
    private static $runningSumPointName;
    private static $sumpoints;
    private static $serverTimingHeader = [];
    private static $started = false;
    private static $dumps = [];

    private static function start()
    {
        if (!self::$started) {
            Debugger::timer("_app");
            self::$started = true;
        }
    }

    /**
     * Create measured checkpoint for time measurements. Every checkpoint ends previous checkpoint.
     * Optionally, checkpoint can contain name, which will be written in log
     *
     * @param string $name
     * @param bool $sum If this is a summed checkpoint
     */
    public static function checkpoint($name = null)
    {
        self::start();

        self::finishRunningPoint();

        self::startNewPoint($name);
    }

    /**
     * Create measured summed checkpoint for time measurements. Ends previous summed checkpoint and adds its spent time into time sums.
     *
     * @param string $name
     */
    public static function sumpoint($name)
    {
        self::start();

        self::finishRunningSumPoint();

        self::startNewSumPoint($name);
    }

    private static function finishRunningPoint()
    {
        $active = is_int(self::$number);

        if ($active) {
            self::$points[self::$number]["time"] = Debugger::timer(self::$number);
        }
    }

    private static function finishRunningSumPoint()
    {
        $active = !empty(self::$sumNumber);

        if ($active) {
            //end time of previously running sum point
            self::$sumpoints[self::$sumNumber]["time"] = Debugger::timer("_sum_" . self::$sumNumber);
        }
    }

    private static function startNewPoint($name = null)
    {
        self::$number = is_int(self::$number) ? self::$number + 1 : 0;
        self::$points[self::$number] = [
            "name" => $name ?? "timer-" . self::$number,
            "time" => null
        ];
        Debugger::timer(self::$number);
    }

    private static function startNewSumPoint($name)
    {
        self::$sumNumber = is_int(self::$sumNumber) ? self::$sumNumber + 1 : 0;
        self::$sumpoints[self::$sumNumber] = [
            "name" => $name,
            "time" => 0
        ];
        self::$runningSumPointName = $name;
        Debugger::timer("_sum_" . self::$sumNumber);
    }

    /**
     * Ends time measuring - stops last timer and logs all checkpoints into info.log
     */
    public static function end()
    {
        self::finishRunningPoint();

        if (!empty(self::$sumpoints) && end(self::$sumpoints)["time"] == 0) {
            self::finishRunningSumPoint();
        }

        $timeWholeApp = Debugger::timer("_app");

        self::logPoints($timeWholeApp);
        self::logSumPoints($timeWholeApp);

        Debugger::log("**** Time of whole measurement: " . (self::toMs($timeWholeApp)), "timer");
        self::addServerTime("complete", null, $timeWholeApp);
        self::setServerTimingApiHeader();
        self::$dumps["time"] = self::toMs($timeWholeApp);
        Debugger::barDump(self::$dumps);
    }

    private static function logPoints($timeWholeApp)
    {
        Debugger::log("**** Timer points: ****", "timer");

        if (empty(self::$points)) {
            Debugger::log("No points specified", "timer");
            return;
        }

        self::$dumps["timer_points"] = [];


        foreach (self::$points as $index => $timer) {
            $timerName = $timer["name"];
            $nextTimerName = array_key_exists($index + 1, self::$points) ? self::$points[$index + 1]["name"] : "end";
            $time = $timer["time"];
            $timePrecentage = round(($timer["time"] / $timeWholeApp) * 100, 2);
            $logTxt = "Timer '$timerName' -> $nextTimerName ... " . self::toMs($time) . " [$timePrecentage %]";
            self::addServerTime($timerName, $nextTimerName, $time);
            Debugger::log($logTxt, "timer");
            self::$dumps["timer_points"][] = $logTxt;
        }
    }

    private static function addServerTime(string $fromName, ?string $toName = null, float $time = 0.0)
    {
        $caption = $toName ? Strings::webalize($fromName) . "..." . Strings::webalize($toName) : Strings::webalize($fromName);
        self::$serverTimingHeader[] = "$caption;dur=" . intval(round((float)$time * 1000, 0));
    }

    private static function setServerTimingApiHeader()
    {
        $decades = count(self::$serverTimingHeader) > 9 ? 2 : 1;

        if (count(self::$serverTimingHeader) > 99) {
            $decades = 3;
        };
        $headerStrings = [];
        foreach (self::$serverTimingHeader as $index => $val) {
            $headerStrings[] = ($decades > 1 ? sprintf("%0{$decades}d", $index) : $index) . "-$val";
        }

        if (!headers_sent()) {
            header("Server-Timing: " . join(", ", $headerStrings));
        }
    }

    private static function logSumPoints($timeWholeApp)
    {
        Debugger::log("**** Timer sumpoints: ****", "timer");
        if (empty(self::$sumpoints)) {
            Debugger::log("No sumpoints specified", "timer");
            return;
        }

        self::$dumps["timer_sumpoints"] = [];

        $sums = [];
        foreach (self::$sumpoints as $timer) {
            $name = $timer["name"];
            if (array_key_exists($name, $sums)) {
                $sums[$name] += $timer["time"];
            } else {
                $sums[$name] = $timer["time"];
            }
        }

        $line = null;
        $previousName = null;

        foreach ($sums as $name => $time) {
            if ($line) {
                Debugger::log(str_replace("_NEXTTIMERNAME_", $name, $line), "timer");
                self::addServerTime("sum:" . $previousName, $name, $previousTime);
            }
            $timePrecentage = round(($timer["time"] / $timeWholeApp) * 100, 2);
            $line = "Timer '$name' -> _NEXTTIMERNAME_ ... " . self::toMs($time) . " [$timePrecentage %]";
            $previousName = $name;
            $previousTime = $time;
        }

        if ($line) {
            $logTxt = str_replace("_NEXTTIMERNAME_", "end", $line);
            Debugger::log($logTxt, "timer");
            self::addServerTime("sum:" . $previousName, "end", $previousTime);
            self::$dumps["timer_sumpoints"][] = $logTxt;
        }
    }

    private static function toMs($time)
    {
        return round((float)$time * 1000, 3) . " ms";
    }
}