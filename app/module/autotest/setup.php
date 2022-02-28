<?php

use Tester\Runner\Output\ConsolePrinter;
use Tester\Runner\Output\JUnitPrinter;
use Tymy\Module\Autotest\Manager\TestsManager;

/*
 * Setup file for nette runner
 * You can use variable $runner here, which is created instance of Test Runner
 */

if (isset($runner)) {
    $runner->setEnvironmentVariable("ROOT_DIR", ROOT_DIR);
    $runner->setEnvironmentVariable("TEAM_DIR", TEAM_DIR);
    $runner->setEnvironmentVariable("AUTOTEST", true);

    if (!file_exists(TEAM_DIR . "/log_autotest")) {
        mkdir(TEAM_DIR . "/log_autotest");
    }
    if (!file_exists(TEAM_DIR . "/temp_autotest")) {
        mkdir(TEAM_DIR . "/temp_autotest");
    }

    $runner->outputHandlers = [
        new ConsolePrinter($runner, true, TestsManager::OUT_CONSOLE),
        new JUnitPrinter(TestsManager::OUT_JUNIT),
    ];
}
