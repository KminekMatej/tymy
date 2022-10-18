<?php

namespace Tymy\Module\Autotest\Manager;

use Tester\Runner\CliTester;

use const ROOT_DIR;
use const TEST_DIR;

/**
 * Description of TestsManager
 *
 * @author Matěj Kmínek
 */
class TestsManager
{
    public const SKIPS = ["_SharedTesters","_template"];
    public const OUT_JUNIT = ROOT_DIR . "/temp/output.junit";
    public const OUT_CONSOLE = ROOT_DIR . "/temp/output.console";

    public function runTests($folder = ""): ?int
    {
        $command = "tester -c " . TEST_DIR . "/php.ini -o junit --setup " . TEST_DIR . "/setup.php -j 4 " . TEST_DIR . "/app";

        if ($folder && $folder != "all") {
            $command .= "/" . $folder;
        }
        $argv = explode(' ', $command);

        $_SERVER['argv'] = $argv;
        $_SERVER['argc'] = count($argv);

        require ROOT_DIR . '/vendor/nette/tester/src/Runner/Test.php';
        require ROOT_DIR . '/vendor/nette/tester/src/Runner/PhpInterpreter.php';
        require ROOT_DIR . '/vendor/nette/tester/src/Runner/Runner.php';
        require ROOT_DIR . '/vendor/nette/tester/src/Runner/CliTester.php';
        require ROOT_DIR . '/vendor/nette/tester/src/Runner/Job.php';
        require ROOT_DIR . '/vendor/nette/tester/src/Runner/CommandLine.php';
        require ROOT_DIR . '/vendor/nette/tester/src/Runner/TestHandler.php';
        require ROOT_DIR . '/vendor/nette/tester/src/Runner/OutputHandler.php';
        require ROOT_DIR . '/vendor/nette/tester/src/Runner/Output/Logger.php';
        require ROOT_DIR . '/vendor/nette/tester/src/Runner/Output/TapPrinter.php';
        require ROOT_DIR . '/vendor/nette/tester/src/Runner/Output/ConsolePrinter.php';
        require ROOT_DIR . '/vendor/nette/tester/src/Runner/Output/JUnitPrinter.php';
        require ROOT_DIR . '/vendor/nette/tester/src/Framework/Helpers.php';
        require ROOT_DIR . '/vendor/nette/tester/src/Framework/Environment.php';
        require ROOT_DIR . '/vendor/nette/tester/src/Framework/Assert.php';
        require ROOT_DIR . '/vendor/nette/tester/src/Framework/AssertException.php';
        require ROOT_DIR . '/vendor/nette/tester/src/Framework/Dumper.php';
        require ROOT_DIR . '/vendor/nette/tester/src/Framework/DataProvider.php';
        require ROOT_DIR . '/vendor/nette/tester/src/Framework/TestCase.php';
        require ROOT_DIR . '/vendor/nette/tester/src/CodeCoverage/Collector.php';
        require ROOT_DIR . '/vendor/nette/tester/src/CodeCoverage/PhpParser.php';
        require ROOT_DIR . '/vendor/nette/tester/src/CodeCoverage/Generators/AbstractGenerator.php';
        require ROOT_DIR . '/vendor/nette/tester/src/CodeCoverage/Generators/HtmlGenerator.php';
        require ROOT_DIR . '/vendor/nette/tester/src/CodeCoverage/Generators/CloverXMLGenerator.php';

        return (new CliTester())->run();
    }

    /**
     * Load all folders with tests
     *
     * @return string[] Array of folders, with exceptions set in SKIP constant
     */
    public function getAllFolders(): array
    {
        $dirs = array_filter(glob(TEST_DIR . '/app/*'), 'is_dir');
        $modules = ["all"];

        foreach ($dirs as $fullDir) {
            $mod = basename($fullDir);
            if (in_array($mod, self::SKIPS)) {
                continue;
            }
            $modules[] = $mod;
        }
        return $modules;
    }
}
