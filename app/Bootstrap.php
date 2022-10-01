<?php

// phpcs:disable PSR1.Files.SideEffects

declare(strict_types=1);

namespace Tymy;

use Exception;
use Nette\Configurator;
use Nette\DI\Container;
use Nette\Neon\Neon;

use const ROOT_DIR;

require __DIR__ . '/../vendor/autoload.php';

class Bootstrap
{
    public const MODULES_DIR = ROOT_DIR . "/app/module";

    public static function boot(): Container
    {
        // absolute filesystem path to the application root
        define("ROOT_DIR", getenv("ROOT_DIR") ? self::normalizePath(getenv("ROOT_DIR")) : self::normalizePath(__DIR__ . "/.."));
        define("TEAM_DIR", getenv("TEAM_DIR") ?: str_replace("//", "/", dirname($_SERVER['SCRIPT_FILENAME'], 2)));
        define('MODULES', array_diff(scandir(self::MODULES_DIR), ['..', '.']));

        $autotestMode = getenv("AUTOTEST") || isset($_GET["AUTOTEST"]);

        $configurator = new Configurator();

        try {   // debug.local.neon contains either true, to generally enable debug, or array of IP addresses
            $debugFile = TEAM_DIR . '/local/debug.neon';
            $debug = file_exists($debugFile) ? Neon::decode(file_get_contents($debugFile)) : false;
        } catch (Exception) {
            $debug = false;
        }

        $configurator->setDebugMode($debug ?: false);

        $configurator->enableTracy($autotestMode ? TEAM_DIR . '/log_autotest' : TEAM_DIR . '/log');

        $configurator->setTempDirectory($autotestMode ? TEAM_DIR . '/temp_autotest' : TEAM_DIR . '/temp');

        $configurator->createRobotLoader()
            ->addDirectory(__DIR__)
            ->register();

        $configurator->addConfig(TEAM_DIR . '/app/config/config.neon');
        $configurator->addConfig(TEAM_DIR . '/local/config.neon');

        $configurator->addParameters(["team" => getenv("team") ?: substr($_SERVER["HTTP_HOST"], 0, strpos($_SERVER["HTTP_HOST"], "."))]);

        self::addModuleConfig($configurator, MODULES);

        return $configurator->createContainer();
    }

    /**
     * Path normalizer - removes double dots from path to make it look clearer
     */
    public static function normalizePath(string $path): string
    {
        $root = ($path[0] === '/') ? '/' : '';

        $segments = explode('/', trim($path, '/'));
        $ret = [];
        foreach ($segments as $segment) {
            if (($segment == '.') || $segment === '') {
                continue;
            }
            if ($segment == '..') {
                array_pop($ret);
            } else {
                $ret[] = $segment;
            }
        }
        return $root . implode('/', $ret);
    }

    /**
     * Enrich configurator with config files from all of the submodules
     */
    private static function addModuleConfig(Configurator &$configurator, array $modules): void
    {
        foreach ($modules as $module) {
            if ($module == "." || $module == "..") {
                continue;
            }

            $configFile = self::MODULES_DIR . "/$module/config/config.neon";

            if (file_exists($configFile)) {
                $configurator->addConfig($configFile);
            }
        }
    }
}
