<?php

declare(strict_types=1);

namespace Tymy;

use Nette\Configurator;
use Nette\Neon\Neon;

require __DIR__ . '/../vendor/autoload.php';

class Bootstrap
{
    public const MODULES_DIR = ROOT_DIR . "/app/modules";

    public static function boot(): Container
    {
        // absolute filesystem path to the application root
        define("ROOT_DIR", getenv("ROOT_DIR") ? self::normalizePath(getenv("ROOT_DIR")) : self::normalizePath(__DIR__ . "/.."));

        define('TMP_FOLDER', ROOT_DIR . "/temp/tymy.cz");

        $configurator = new Configurator;

        try {   // debug.local.neon contains either true, to generally enable debug, or array of IP addresses
            $debugFile = ROOT_DIR . '/app/config/debug.local.neon';
            $debug = file_exists($debugFile) ? Neon::decode(file_get_contents(__DIR__ . '/config/debug.local.neon')) : false;
        } catch (Exception $exc) {
            $debug = false;
        }

        $configurator->setDebugMode($debug ? $debug : false);

        $configurator->enableTracy(ROOT_DIR . '/log');

        $configurator->setTimeZone('Europe/Prague');
        $configurator->setTempDirectory(ROOT_DIR . '/temp');

        $configurator->createRobotLoader()
                ->addDirectory(__DIR__)
                ->register();

        $configurator->addConfig(__DIR__ . '/config/config.neon');
        $configurator->addConfig(__DIR__ . '/config/config.local.neon');

        $configurator->addParameters(["team" => getenv("team") ?: substr($_SERVER["HTTP_HOST"], 0, strpos($_SERVER["HTTP_HOST"], "."))]);

        $modules = scandir(self::MODULES_DIR);

        self::addModuleConfig($configurator, $modules);

        $container = $configurator->createContainer();

        self::addModuleRoutes($container, $modules);

        return $container;
    }

    /**
     * Path normalizer - removes double dots from path to make it look clearer
     * 
     * @param string $path
     * @return string
     */
    public static function normalizePath(string $path): string
    {
        $root = ($path[0] === '/') ? '/' : '';

        $segments = explode('/', trim($path, '/'));
        $ret = array();
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
        return $root . implode('/', $ret);
    }

    /**
     * Enrich configurator with config files from all of the submodules
     * 
     * @param Configurator $configurator
     * @param array $modules
     * @return void
     */
    private static function addModuleConfig(Configurator &$configurator, array $modules): void
    {
        foreach ($modules as $module) {
            if ($module == "." || $module == "..") {
                continue;
            }

            $configFile = self::MODULES_DIR . '/config/config.neon';

            if (file_exists($configFile)) {
                $configurator->addConfig($configFile);
            }
        }
    }

    /**
     * Enrich created container with routes from modules
     * Module routes must be named routes.module (routes.address, routes.calendar etc) - to be taken into account
     * 
     * @param Container $container
     * @param array $modules
     * @return void
     */
    private static function addModuleRoutes(Container &$container, array $modules): void
    {
        /* @var $routerFactory RouterFactory */
        $routerFactory = $container->getByType(RouterFactory::class);

        foreach ($modules as $module) {
            if ($module == "." || $module == "..") {
                continue;
            }

            $serviceName = lcfirst(str_replace('-', '', ucwords($module, '-')));

            if ($container->hasService('routes.' . $serviceName)) {
                $routesFactory = $container->getService('routes.' . $serviceName);
                $routerFactory->addModuleRoutes($routesFactory->createRoutes());
            }
        }
    }
}