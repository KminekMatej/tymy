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
    private static ?int $number = null;
    private static ?int $sumNumber = null;
    /**
     * @var array<mixed, array<string, mixed>>|null
     */
    private static ?array $points = null;
    /**
     * @var array<mixed, array<string, mixed>>|null
     */
    private static ?array $sumpoints = null;
    private static array $serverTimingHeader = [];
    private static bool $started = false;
    private static array $dumps = [];

    private static function start(): void
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
     */
    public static function checkpoint($name = null): void
    {
        self::start();

        self::finishRunningPoint();

        self::startNewPoint($name);
    }

    /**
     * Create measured summed checkpoint for time measurements. Ends previous summed checkpoint and adds its spent time into time sums.
     */
    public static function sumpoint(string $name): void
    {
        self::start();

        self::finishRunningSumPoint();

        self::startNewSumPoint($name);
    }

    private static function finishRunningPoint(): void
    {
        $active = is_int(self::$number);

        if ($active) {
            self::$points[self::$number]["time"] = Debugger::timer(self::$number);
        }
    }

    private static function finishRunningSumPoint(): void
    {
        $active = !empty(self::$sumNumber);

        if ($active) {
            //end time of previously running sum point
            self::$sumpoints[self::$sumNumber]["time"] = Debugger::timer("_sum_" . self::$sumNumber);
        }
    }

    private static function startNewPoint($name = null): void
    {
        self::$number = is_int(self::$number) ? self::$number + 1 : 0;
        self::$points[self::$number] = [
            "name" => $name ?? "timer-" . self::$number,
            "time" => null
        ];
        Debugger::timer(self::$number);
    }

    private static function startNewSumPoint($name): void
    {
        self::$sumNumber = is_int(self::$sumNumber) ? self::$sumNumber + 1 : 0;
        self::$sumpoints[self::$sumNumber] = [
            "name" => $name,
            "time" => 0
        ];
        Debugger::timer("_sum_" . self::$sumNumber);
    }

    /**
     * Ends time measuring - stops last timer and logs all checkpoints into info.log
     */
    public static function end(): void
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

    private static function logPoints($timeWholeApp): void
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

    private static function addServerTime(string $fromName, ?string $toName = null, float $time = 0.0): void
    {
        $caption = $toName ? Strings::webalize($fromName) . "..." . Strings::webalize($toName) : Strings::webalize($fromName);
        self::$serverTimingHeader[] = "$caption;dur=" . (int) round($time * 1000, 0);
    }

    private static function setServerTimingApiHeader(): void
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
            header("Server-Timing: " . implode(", ", $headerStrings));
        }
    }

    private static function logSumPoints($timeWholeApp): void
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
                self::addServerTime("sum:" . $previousName, $name, $previousTime ?? 0.0);
            }
            $timePrecentage = round(($timer["time"] / $timeWholeApp) * 100, 2);
            $line = "Timer '$name' -> _NEXTTIMERNAME_ ... " . self::toMs($time) . " [$timePrecentage %]";
            $previousName = $name;
            $previousTime = $time;
        }
        $logTxt = str_replace("_NEXTTIMERNAME_", "end", $line);
        Debugger::log($logTxt, "timer");
        self::addServerTime("sum:" . $previousName, "end", $previousTime ?? 0.0);
        self::$dumps["timer_sumpoints"][] = $logTxt;
    }

    private static function toMs($time): string
    {
        return round((float)$time * 1000, 3) . " ms";
    }
}
