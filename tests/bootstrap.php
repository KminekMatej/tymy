<?php

use Tester\Runner\Runner;

/** @var \Tester\Runner\Runner $runner */

require __DIR__ . '/../vendor/autoload.php';
$testedTeam = json_decode(file_get_contents(__DIR__ . "/test.json"));
$_SERVER["HTTP_HOST"] = $testedTeam->team . "." . $testedTeam->root;
$GLOBALS["testedTeam"] = (array)$testedTeam;
$GLOBALS["dateRegex"] = '/^(?:[1-9]\d{3}-(?:(?:0[1-9]|1[0-2])-(?:0[1-9]|1\d|2[0-8])|(?:0[13-9]|1[0-2])-(?:29|30)|(?:0[13578]|1[02])-31)|(?:[1-9]\d(?:0[48]|[2468][048]|[13579][26])|(?:[2468][048]|[13579][26])00)-02-29)T(?:[01]\d|2[0-3]):[0-5]\d:[0-5]\d(?:Z|[+-][01]\d:[0-5]\d)$/m';
Tester\Environment::setup();
$configurator = new Nette\Configurator();
$configurator->setDebugMode(false);
$configurator->setTempDirectory(__DIR__ . '/../temp');
$configurator->createRobotLoader()
    ->addDirectory(__DIR__ . '/../app')
        ->addDirectory(__DIR__)
    ->register();
$configurator->addConfig(__DIR__ . '/../app/config/config.neon');
$configurator->addConfig(__DIR__ . '/../app/config/config.local.neon');
return $configurator->createContainer();
