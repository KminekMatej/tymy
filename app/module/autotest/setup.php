<?php

use Nette\Utils\FileSystem;
use Tester\Runner\Runner;

/*
 * Setup file for nette runner
 * You can use variable $runner here, which is created instance of Test Runner
 */

if (isset($runner) && $runner instanceof Runner) {
    require_once __DIR__ . "/../../../vendor/autoload.php";

    $rootDir = FileSystem::normalizePath(__DIR__ . "/../../..");
    putenv("ROOT_DIR=$rootDir");
    putenv("TEAM_DIR=$rootDir");
    putenv("team=autotest");

    $runner->setEnvironmentVariable("AUTOTEST", true);
}
