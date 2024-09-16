<?php

// phpcs:disable PSR1.Files.SideEffects

namespace Tymy\Bin;

use Nette\Caching\Storages\FileStorage;
use Nette\Database\Explorer;
use Nette\DI\Container;
use Nette\Neon\Entity;
use Nette\Neon\Neon;
use Nette\Utils\DateTime;
use Nette\Utils\FileSystem;
use PDO;
use PDOException;
use Tymy\Bootstrap;
use Tymy\Module\Admin\Manager\MigrationManager;
use Tymy\Module\Autotest\Manager\MockMailer;
use Tymy\Module\Autotest\MockRequestFactory;

use const ROOT_DIR;
use const TEST_DIR;

require(__DIR__ . "/Common.php");
require(__DIR__ . "/../app/Bootstrap.php");

/**
 * CLI script to run autotests automatically
 */
class Tester
{
    public const TEMP_DIR = "temp_autotest";
    public const LOG_DIR = "log_autotest";

    private string $path;
    private string $command = "run";
    private ?string $mail = null;
    private string|bool $coverage = false;
    private array $log = [];
    private string $configFile;
    private string $autotestConfigFile;
    private array $configuration;
    private ?Container $container = null;
    private MigrationManager $migrationManager;

    public function __construct()
    {
        define("ROOT_DIR", FileSystem::normalizePath(Common::getCwdUnresolved() . "/.."));
        define("TEAM_DIR", ROOT_DIR);
        define("TEST_DIR", ROOT_DIR . "/app/module/autotest");
        putenv("team=autotest");
        $this->configFile = ROOT_DIR . "/local/config.neon";
        $this->autotestConfigFile = ROOT_DIR . "/local/config.autotest.neon";
    }

    /**
     * @param array $args In-line array of arguments
     */
    private function loadArguments($args)
    {
        array_shift($args); //drop first parameter (script name)

        if (empty($args)) {   //when no parameters specified, simply print help and exit
            $this->help();
            exit();
        }

        while (count($args)) {
            $arg = array_shift($args);

            switch ($arg) {
                case "-h":
                case "--help":
                    $this->help();
                    exit();
                    break;
                case "-m":
                case "--mail":
                    $this->mail = array_shift($args);
                    break;
                case "-c":
                case "--coverage":
                    $this->coverage = true;
                    break;
                case "create-env":
                case "delete-env":
                case "migrate":
                case "reset":
                    $this->command = $arg;
                    break;
                default:
                    $this->path = str_starts_with($arg, "/") ? $arg : FileSystem::normalizePath(__DIR__ . "/" . $arg);

                    if (!file_exists($this->path)) {
                        echo "\nUnrecognized path: [{$this->path}]\n";
                        exit(1);
                    }
            }
        }
    }

    public function run($args)
    {
        $this->loadArguments($args);

        switch ($this->command) {
            case "run":
                $this->runTests();
                break;
            case "create-env":
                $this->createTestEnvironment();
                break;
            case "delete-env":
                $this->deleteTestEnvironment();
                break;
            case "migrate":
                $this->migrate();
                break;
            case "reset":
                $this->resetDatabase();
                break;
            default:
                die("Unknown command supplied");
                break;
        }
    }

    private function initContainer(): void
    {
        if (!$this->container) {
            $this->container = Bootstrap::boot();
            $this->migrationManager = $this->container->getByType(MigrationManager::class);
        }
    }

    /**
     * Migrate test database to latest possible version
     */
    private function migrate()
    {
        $this->logg("Starting migration");

        $serverName = $this->getServerNameFromDir();

        $_SERVER["SERVER_NAME"] = $serverName;

        putenv("AUTOTEST=1");
        require_once ROOT_DIR . '/vendor/autoload.php';

        $this->initContainer();

        $output = $this->migrationManager->migrateUp();

        foreach ($output["log"] as $logLine) {
            $this->logg($logLine);
        }

        if ($output["success"]) {
            $this->successLogg("Migration finished succesfully.");
        } else {
            $this->errLogg("Migration failed. Please see the migration log above to detect the failure.");
        }
    }

    /**
     * Run test database update query upon the database
     *
     * @return void
     */
    private function resetDatabase(): void
    {
        $this->logg("Reloading test database data");

        $serverName = $this->getServerNameFromDir();

        $_SERVER["SERVER_NAME"] = $serverName;

        putenv("AUTOTEST=1");
        require_once ROOT_DIR . '/vendor/autoload.php';

        if (!$this->container) {
            $this->container = Bootstrap::boot();
        }
        $migrationManager = $this->container->getByType(MigrationManager::class);

        /* @var $migrationManager MigrationManager */
        $commands = $migrationManager->getCommands(file_get_contents(TEST_DIR . "/data.sql"));

        foreach ($commands as $command) {
            $start = round(microtime(true) * 1000);
            $migrationManager->executeCommands([$command]);
            $duration = round(microtime(true) * 1000) - $start;
            $this->logg("'$command' ... $duration ms");
        }

        $this->createAutotestTempDirectory();
    }

    private function getServerNameFromDir(): string
    {
        return basename(dirname(ROOT_DIR, 1));
    }

    private function runTests()
    {
        $this->logg("Running tests");

        foreach ([ROOT_DIR . "/log_autotest/requests.log", ROOT_DIR . "/temp_autotest/apidoc.json", ROOT_DIR . "/temp/apidoc.json"] as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }

        $serverName = $this->getServerNameFromDir();

        putenv("SERVER_NAME=$serverName");
        putenv("ROOT_DIR={ROOT_DIR}");

        $testerParams = [
            ROOT_DIR . "/vendor/nette/tester/src/tester.php", //first param is the path of test file
            "-j",
            "4", //tests are run in 4 processes
            "-c",
            TEST_DIR . "/php.ini", //using its own php.ini file
            "--setup",
            TEST_DIR . "/setup.php", //using setup for environment separation
        ];

        if ($this->coverage) {
            $testerParams[] = "--coverage=" . ROOT_DIR . "/log/coverage.html";
            $testerParams[] = "--coverage-src=" . ROOT_DIR . "/app";
        }

        $testerParams[] = $this->path;

        $this->logg(join(" ", $testerParams));

        $_SERVER["argv"] = $testerParams;

        require $testerParams[0];
    }

    private function createTestEnvironment()
    {
        $this->logg("Creating test environment");

        $this->configuration = $this->loadConfiguration($this->configFile);

        $this->createAutotestTempDirectory();
        $this->createAutotestLogDirectory();

        $teamDb = $this->loadDatabaseCredentials($this->configuration, "team");
        $mainDb = $this->loadDatabaseCredentials($this->configuration, "main");

        $teamAutotestDbName = $teamDb["name"] . "__autotest";
        $mainAutotestDbName = $mainDb["name"] . "__autotest";

        //Create duplicate config file
        $this->createAutotestConfigFile($this->configuration, $teamDb["host"], $teamAutotestDbName, $mainAutotestDbName);

        $pdo = new PDO($teamDb["dsn"], $teamDb["user"], $teamDb["password"]);
        //Prepare team database
        $this->dropDatabase($pdo, $teamAutotestDbName, $teamDb["user"], $teamDb["host"]); //drop team DB if exists
        $this->createDatabase($pdo, $teamAutotestDbName, $teamDb["user"], $teamDb["host"]); //create team DB new
        $this->migrate();
        $this->resetDatabase();

        //Prepare main database
        $this->dropDatabase($pdo, $mainAutotestDbName, $teamDb["user"], $teamDb["host"]); //drop main DB if exists
        $this->createDatabase($pdo, $mainAutotestDbName, $teamDb["user"], $teamDb["host"]); //create main DB new
        $this->importMainDatabase($mainAutotestDbName);

        //Symlink test directory
        $this->rmTestsSymlink();
        $this->symlinkTestDir();
    }

    /**
     * Load database connection info frorm configuration array
     *
     * @param array $configuration
     * @return array [ "host" => (string), "name" => (string), "user" => (string), "password" => (string) ],
     */
    private function loadDatabaseCredentials(array $configuration, ?string $mark = null): array
    {
        //get autotest database name
        $dbConfig = $this->getDbConfig($configuration, $mark);
        $originalDsn = $dbConfig["dsn"];
        preg_match('/mysql:host=(.*);dbname=(.*)/m', $originalDsn, $matches);

        return [
            "dsn" => $originalDsn,
            "host" => $matches[1],
            "name" => $matches[2],
            "user" => $dbConfig["user"],
            "password" => $dbConfig["password"],
        ];
    }

    private function deleteTestEnvironment()
    {
        $this->logg("Deleting test environment");

        $this->configuration = $this->loadConfiguration($this->autotestConfigFile);

        $this->deleteAutotestTempDirectory();
        $this->deleteAutotestLogDirectory();

        if (file_exists($this->autotestConfigFile)) {
            $this->logg("Deleting autotest config file");
            unlink($this->autotestConfigFile);
            $this->successLogg("Autotest config file {$this->autotestConfigFile} deleted");
        }

        $teamDb = $this->loadDatabaseCredentials($this->configuration, "team");
        $mainDb = $this->loadDatabaseCredentials($this->configuration, "main");

        //Drop created databases if exists
        $pdo = new PDO($teamDb["dsn"], $teamDb["user"], $teamDb["password"]);

        $this->dropDatabase($pdo, $teamDb["name"], $teamDb["user"], $teamDb["host"]); //drop team DB if exists
        $this->dropDatabase($pdo, $mainDb["name"], $mainDb["user"], $mainDb["host"]); //drop main DB if exists

        $this->rmTestsSymlink();
    }

    private function createAutotestConfigFile(array $configuration, string $dbHost, string $teamDbName, string $mainDbName)
    {
        $this->logg("Creating autotest config file");

        $configuration["database"]["team"]["dsn"] = "mysql:host=$dbHost;dbname=$teamDbName";
        $configuration["database"]["main"]["dsn"] = "mysql:host=$dbHost;dbname=$mainDbName";

        if (!array_key_exists("services", $configuration)) {
            $configuration["services"] = [];
        }
        unset($configuration["mail"]);
        $configuration["services"]["mail.mailer"] = MockMailer::class;
        $configuration["services"]["http.requestFactory"] = MockRequestFactory::class;

        $configuration["services"]["cacheStorage"] = ["factory" => new Entity(FileStorage::class, ["%tempDir%"])];

        $configuration["parameters"]["web-push"]["VAPID"]["publicKey"] = "BIxsETXXdXYdeutK9cPKTxifri3tndIJkxP-A8tx2A_Iwb4042QiCib6NrtqijHJndjYcnUcXIysrbSwH9lfous"; //mock VAPID
        $configuration["parameters"]["web-push"]["VAPID"]["privateKey"] = "b7tq-dJ5wiOjXAh_zgG45Ls1NBegSDOl_Xhlc-upYPU";

        file_put_contents($this->autotestConfigFile, Neon::encode($configuration, Neon::BLOCK));

        $this->successLogg("Autotest config file {$this->autotestConfigFile} succesfully created");
    }

    private function symlinkTestDir()
    {
        $symlink = __DIR__ . "/tests";
        $target = "../app/module/autotest/app";

        $this->logg("Creating symlink $symlink to $target directory");

        symlink($target, $symlink);
        $this->logg("Symlink succesfully created");
    }

    private function rmTestsSymlink()
    {
        $symlink = __DIR__ . "/tests";
        if (file_exists($symlink) && is_link($symlink)) {
            $target = "../app/module/autotest/app";
            $this->logg("Removing symlink $symlink to $target directory");
            unlink($symlink);
            $this->logg("Symlink succesfully removed");
        }
    }

    /**
     * Drop database of desired name using existing PDO instance
     *
     * @param PDO $pdo
     * @param string $dbName
     * @return void
     */
    private function dropDatabase(PDO $pdo, string $dbName, string $dbUser, string $dbHost): void
    {
        $this->logg("Dropping database $dbName");
        try {
            $pdo->query("DROP DATABASE IF EXISTS `$dbName`;");
        } catch (PDOException $exc) {
            $this->warnLogg("Deleting database $dbName failed. Perhaps you should give user GRANT privileges on $dbName database: GRANT ALL PRIVILEGES ON `$dbName`.* TO '$dbUser'@`$dbHost`;");
            throw $exc;
        }
        $this->successLogg("Database $dbName dropped");
    }

    /**
     * Create database of desired name, using existing PDO instance
     *
     * @param PDO $pdo
     * @param string $dbName
     * @return void
     */
    private function createDatabase(PDO $pdo, string $dbName, string $dbUser, string $dbHost): void
    {
        $this->logg("Creating database $dbName");
        try {
            $pdo->query("CREATE DATABASE `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;");
        } catch (PDOException $exc) {
            $this->warnLogg("Creating database $dbName failed. Perhaps you should give user GRANT privileges on $dbName database: GRANT ALL PRIVILEGES ON $dbName.* TO '$dbUser'@'$dbHost';");
            throw $exc;
        }
        $this->successLogg("Database $dbName created");
    }

    /**
     * Create autotest temp directory. If it already exists, will be cleaned
     *
     * @return void
     */
    private function createAutotestTempDirectory(): void
    {
        $this->deleteAutotestTempDirectory();
        $this->logg("Creating temp directory");
        mkdir(ROOT_DIR . "/" . self::TEMP_DIR);
    }

    /**
     * Delete autotest temp directory.
     *
     * @return void
     */
    private function deleteAutotestTempDirectory(): void
    {
        $autotestTempDir = ROOT_DIR . "/" . self::TEMP_DIR;
        $this->logg("Deleting temp directory");
        if (file_exists($autotestTempDir)) {
            $this->rrmDir($autotestTempDir);
        }
    }

    /**
     * Create autotest log directory. If it already exists, will be cleaned
     *
     * @return void
     */
    private function createAutotestLogDirectory(): void
    {
        $this->deleteAutotestLogDirectory();
        $this->logg("Creating log directory");
        mkdir(ROOT_DIR . "/" . self::LOG_DIR);
    }

    /**
     * Delete autotest log directory.
     *
     * @return void
     */
    private function deleteAutotestLogDirectory(): void
    {
        $autotestTempDir = ROOT_DIR . "/" . self::LOG_DIR;
        $this->logg("Deleting log directory");
        if (file_exists($autotestTempDir)) {
            $this->rrmDir($autotestTempDir);
        }
    }

    private function importMainDatabase()
    {
        $this->logg("Importing structure of main database");
        $mainDatabase = $this->container->getByName("database.main.explorer");
        assert($mainDatabase instanceof Explorer);

        //load custom data into database
        $this->migrationManager->executeSqlContents(file_get_contents(TEST_DIR . "/main-database.sql"), $this->log, $mainDatabase);
        $mainDatabase->table("teams")->insert([
            "name" => 'Autotest Team',
            "sys_name" => 'autotest',
            "languages" => 'CZ,EN',
            "default_lc" => 'CZ',
            "sport" => 'Autotest ultimate',
            "country_id" => 0,
            "modules" => 'WEB,DS_WATCHER,DWNLD,ASK',
            "max_users" => 500,
            "max_events_month" => 100,
            "advertisement" => 'YES',
            "active" => 'YES',
            "retranslate" => 'NO',
            "insert_date" => new DateTime(),
            "time_zone" => 1,
            "dst_flag" => "AUTO",
            "app_version" => "0.2",
            "use_namedays" => 'YES',
            "att_check" => 'FW',
            "att_check_days" => 7,
            "host" => "localhost",
            "tariff" => 'FULL',
            "tariff_until" => '2035-01-01',
            "tariff_payment" => 'YEARLY',
            "required_fields" => 'gender,firstName,lastName,phone,email,birthDate,callName,status,jerseyNumber,street,city,zipCode'
        ]);
    }

    /**
     * Load configuration from specified config neon file
     *
     * @param string $configFile
     * @return array
     */
    private function loadConfiguration(string $configFile): array
    {
        $this->logg("Loading configuration from $configFile");

        if (!file_exists($configFile)) {
            $this->errLogg("File $configFile not found! Aborting");
        }

        foreach (glob(ROOT_DIR . "/vendor/nette/neon/src/Neon/*.php") as $phpFile) {
            require_once $phpFile;
        }
        foreach (glob(ROOT_DIR . "/vendor/nette/neon/src/Neon/Node/*.php") as $phpFile) {
            require_once $phpFile;
        }

        return Neon::decode(file_get_contents($configFile));
    }

    /**
     * Get original dsn from configuration array
     *
     * @param array $configuration
     * @return array
     */
    private function getDbConfig(array $configuration, ?string $mark = null): array
    {
        if (!isset($configuration["database"])) {
            $this->errLogg("DSN config not found in configuration, aborting!");
        }

        return $mark ? $this->configuration["database"][$mark] : $this->configuration["database"];
    }

    private function errLogg(string $msg, bool $die = false)
    {
        $this->logg($msg, "error");
        if ($die) {
            die($msg);
        }
    }

    private function warnLogg(string $msg)
    {
        $this->logg($msg, "warning");
    }

    private function successLogg(string $msg)
    {
        $this->logg($msg, "success");
    }

    private function logg(string $msg, string $type = "info")
    {
        $dt = (new DateTime())->format("j.n.Y H:i:s");

        $logLine = "$dt [$type]: $msg";
        $this->log[] = $logLine;

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

    private function rrmDir($dir, $onlySubfolders = false, array $filesToSkip = [], array $pathsToSkip = [])
    {
        if (is_dir($dir)) {
            $skip = false;
            foreach ($pathsToSkip as $pathToSkip) {
                if (strpos($pathToSkip, $dir) !== false) {
                    $skip = true;
                }
            }
            if ($skip) {
                return;
            }

            $objects = scandir($dir);
            foreach ($objects as $object) {
                if (in_array($object, $filesToSkip)) {
                    continue;
                }

                if ($object != "." && $object != "..") {
                    if (is_dir($dir . DIRECTORY_SEPARATOR . $object) && !is_link($dir . "/" . $object)) {
                        $this->rrmdir($dir . DIRECTORY_SEPARATOR . $object);
                    } else {
                        unlink($dir . DIRECTORY_SEPARATOR . $object);
                    }
                }
            }
            if (!$onlySubfolders) {
                rmdir($dir);
            }
        }
    }

    private function help()
    {
        Common::DUMPLOGO();

        echo "\nTymy.CZ Tester\n"
        . "This script automatically runs Tymy.CZ autotests.\n\n"
        . "Usage: " . Common::BLU . "php tester.php " . Common::ORN . "[command] " . Common::YEL . "[parameters]" . Common::BLK . " [path]\n\n"
        . "Commands: \n"
        . "‾‾‾‾‾‾‾‾ \n"
        . Common::ORN . "update-env" . Common::BLK . "          Takes credentials from config.autotest.local.neon database, and run its cleaning (using SQL file app/tests/data.sql)\n"
        . Common::ORN . "delete-env" . Common::BLK . "          Deletes test database, autotest cache storage and config.autotest.local.neon to wipe everything created by autotester\n"
        . Common::ORN . "migrate" . Common::BLK . "             Migrates test database to latest version\n"
        . Common::ORN . "reset" . Common::BLK . "               Reset test database by triggering test/data.sql commands into test database\n"
        . Common::ORN . "create-env" . Common::BLK . "          Creates test environment, using credentials parsed from config.local.neon file. User must have permissions to create database. Creates database with postfix __autotest, migrates it to latest version and injects autotest data into it. Generates new config.autotest.local.neon file with test credentials and creates folder temp_autotest for different cache storage. Creates symlink directory tests for quicker testing\n"
        . "\nParameters: \n"
        . "‾‾‾‾‾‾‾‾‾‾‾‾ \n"
        . Common::YEL . "-h | --help" . Common::BLK . "         Prints this help and exits\n"
        . Common::YEL . "-m | --mail" . Common::BLK . "         Specify mail address to send coverage report to\n"
        . Common::YEL . "-c | --coverage" . Common::BLK . "     Generate code coverage report after test into log/coverage.html file\n"
        . "\nExamples: \n"
        . "‾‾‾‾‾‾‾‾‾‾‾‾ \n"
        . Common::BLU . "php tester.php " . Common::ORN . "create-env " . Common::BLK . "   Create autotest environment - prepare for tests\n"
        . Common::BLU . "php tester.php " . Common::BLK . "tests/debt    Run all autotests on module debt\n"
        . Common::BLU . "php tester.php " . Common::BLK . "tests    Run all autotests on on every module\n"
        . Common::BLU . "php tester.php " . Common::ORN . "reset " . Common::BLK . "    Reset database to make tests runnable again (in case of some tests weird failures)\n"
        . Common::BLU . "php tester.php " . Common::BLK . "tests " . Common::YEL . "-m 'admin@tymy.cz'" . Common::BLK . "    Run all autotests on on every module\n"
        . Common::BLU . "php tester.php " . Common::BLK . "tests " . Common::YEL . "-c" . Common::BLK . "   Run all autotests with coverage generation\n"
        . Common::BLU . "php tester.php " . Common::ORN . "delete-env " . Common::BLK . "   Clean environment - delete autotest database, log and temp autotest folders\n"
        . Common::BLU . "php tester.php " . Common::YEL . "-h" . Common::BLK . "    Display this help\n";
    }
}

(new Tester())->run($argv);
