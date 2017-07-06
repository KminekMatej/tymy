<?php

require __DIR__ . '/../vendor/autoload.php';

$configurator = new Nette\Configurator;

$configurator->setDebugMode(['94.112.206.90','90.182.181.138', '80.188.249.250', '192.168.16.1', '46.135.80.252']);
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
