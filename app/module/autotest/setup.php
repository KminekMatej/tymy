<?php

use Tester\Runner\Output\ConsolePrinter;
use Tester\Runner\Output\JUnitPrinter;
use Tymy\Module\Autotest\Manager\TestsManager;

/*
 * Setup file for nette runner
 * You can use variable $runner here, which is created instance of Test Runner
 */

$runner->setEnvironmentVariable("ROOT_DIR", ROOT_DIR);

$runner->outputHandlers = [
    new ConsolePrinter($runner, true, TestsManager::OUT_CONSOLE),
    new JUnitPrinter(TestsManager::OUT_JUNIT),
];
