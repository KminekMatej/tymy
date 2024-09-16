<?php

namespace Tymy\Bin;

use Nette\Utils\DateTime;

/**
 * Description of Common
 */
class Common
{
    public const GRN = "\033[92m";
    public const ORN = "\033[93m";
    public const RED = "\033[91m";
    public const BLU = "\033[94m";
    public const YEL = "\033[0;33m";
    public const BLK = "\033[0m";

    private static array $log = [];
    public static bool $verboseMode = false;

    /**
     * Echoes ASCII Boot.space logo to standard output
     *
     * @return void
     */
    public static function DUMPLOGO(): void
    {
        echo <<<'XX'
 _____                 ___ ____
|_   _|  _ _ __ _  _  / __|_  /
  | || || | '  \ || || (__ / / 
  |_| \_, |_|_|_\_, (_)___/___|
      |__/      |__/           

XX;
    }

    public static function errLogg(string $msg, bool $die = false)
    {
        self::logg($msg, false, "error");   //error log
        if ($die) {
            die(1);
        }
    }

    public static function warnLogg(string $msg, bool $verbose = false)
    {
        self::logg($msg, $verbose, "warning");
    }

    public static function successLogg(string $msg, bool $verbose = false)
    {
        self::logg($msg, $verbose, "success");
    }

    public static function logg(string $msg, bool $verbose = false, string $type = "info")
    {
        if ($verbose && !self::$verboseMode) {
            return;
        }

        $dt = (new DateTime())->format("j.n.Y H:i:s");

        $logLine = "$dt [$type]: $msg";
        self::$log[] = $logLine;

        switch ($type) {
            case "success":
                echo "\033[92m $logLine \033[0m\n";
                break;
            case "warning":
                echo "\033[93m $logLine \033[0m\n";
                break;
            case "error":
                echo "\033[91m $logLine \033[0m\n";
                break;

            default:
                echo "\033[94m $logLine \033[0m\n";
                break;
        }
    }

    /**
     * Path normalizer - removes double dots from path to make it look clearer
     *
     * @param string $path
     * @return string
     */
    public static function normalizePath(string $path): string
    {
        $root = ($path[0] === DIRECTORY_SEPARATOR) ? DIRECTORY_SEPARATOR : '';

        $segments = explode(DIRECTORY_SEPARATOR, trim($path, DIRECTORY_SEPARATOR));
        $ret = [];
        foreach ($segments as $segment) {
            if (($segment == '.') || strlen($segment) === 0) {
                continue;
            }
            if ($segment == '..') {
                array_pop($ret);
            } else {
                array_push($ret, $segment);
            }
        }
        return $root . implode(DIRECTORY_SEPARATOR, $ret);
    }

    public static function getCwdUnresolved(): string
    {
        $scriptName = $_SERVER['SCRIPT_FILENAME'];
        $isCli = empty($_SERVER['REMOTE_ADDR']) && !isset($_SERVER['HTTP_USER_AGENT']) && count($_SERVER['argv']) > 0;

        if (!$isCli || strpos($scriptName, "/") === 0 || strpos($scriptName, "~") === 0) {
            return dirname($scriptName);
        }

        return getcwd() . DIRECTORY_SEPARATOR . trim(dirname($scriptName), './');
    }
}
