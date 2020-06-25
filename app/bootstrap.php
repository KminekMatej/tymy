<?php

use Nette\Configurator;
use Nette\Neon\Neon;

require __DIR__ . '/../vendor/autoload.php';

$configurator = new Configurator;

try {   // debug.local.neon contains either true, to generally enable debug, or array of IP addresses
    $debugFile = __DIR__ . '/config/debug.local.neon';
    $debug = file_exists($debugFile) ? Neon::decode(file_get_contents(__DIR__ . '/config/debug.local.neon')) : false;
} catch (Exception $exc) {
    $debug = false;
}

$configurator->setDebugMode($debug ? $debug : false);
$configurator->enableTracy(__DIR__ . '/../log');

$configurator->setTimeZone('Europe/Prague');
$configurator->setTempDirectory(__DIR__ . '/../temp');

$configurator->createRobotLoader()
	->addDirectory(__DIR__)
	->register();

$configurator->addConfig(__DIR__ . '/config/config.neon');
$configurator->addConfig(__DIR__ . '/config/config.local.neon');

$container = $configurator->createContainer();

return $container;
