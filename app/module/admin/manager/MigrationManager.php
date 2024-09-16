<?php

namespace Tymy\Module\Admin\Manager;

use Closure;
use Exception;
use Nette\Database\Explorer;
use Nette\Utils\DateTime;
use PDOException;
use Tracy\Debugger;
use Tymy\Module\Admin\Entity\Migration;
use Tymy\Module\Core\Model\BaseModel;

use function count;

/**
 * Description of MigrationManager
 *
 * @author Matěj Kmínek
 */
class MigrationManager
{
    private const MIGRATION_UP = true;
    private const REGEX_MIGRATION = "\d{4}-\d{2}-\d{2}T\d{2}-\d{2}-\d{2}.sql";
    private const REGEX_BASE = "\d{4}-\d{2}-\d{2}T\d{2}-\d{2}-\d{2}-base.sql";
    private array $log = [];
    private bool $tableExists;
    private array $migrationsCache = [];
    public Closure $logger;

    public function __construct(private Explorer $teamDatabase)
    {
    }

    private function migrationTableExists(): bool
    {
        if (!isset($this->tableExists)) {
            $this->tableExists = !empty($this->teamDatabase->query("SHOW TABLES LIKE 'migration';")->fetchAll());
        }

        return $this->tableExists;
    }

    /**
     * Detect in database which migrations has already been performed and return them
     *
     * @return string[] Array of performed succesfull migrations
     */
    public function getPerformedSuccesfullMigrations(): array
    {
        return $this->tableExists ? $this->teamDatabase->table(Migration::TABLE)->where("result", Migration::RESULT_OK)->fetchPairs(null, "migration") : $this->migrationsCache;
    }

    /**
     * Detect in database latest performed migration and return its number, or null if not found
     */
    public function getLatestMigration(): ?string
    {
        if (!$this->migrationTableExists()) {
            return null;
        }

        return $this->teamDatabase->table(Migration::TABLE)->where("result", Migration::RESULT_OK)->order("migration DESC")->limit(1)->fetchField("migration");
    }

    /**
     * @return mixed[]
     */
    public function executeSqlContents(string $contents, &$log = null, ?Explorer $database = null): array
    {
        if ($log) {
            $this->log = &$log;
        }
        $commands = $this->getCommands($contents);
        return $this->executeCommands($commands, $database);
    }

    /**
     * @return mixed[]
     */
    public function executeCommands(array $sqlCommands, ?Explorer $database = null): array
    {
        $this->logg("Executing supplied " . count($sqlCommands) . " queries");
        foreach ($sqlCommands as $cmd) {
            $cmd = trim($cmd);
            if (substr($cmd, 0, -1) !== ";") {    //add semicolon to the end if its not there
                $cmd .= ";";
            }
            $this->logg("Executing query " . $cmd);
            $database ? $database->query($cmd) : $this->teamDatabase->query($cmd);
        }
        return $this->log;
    }

    public function saveMigrationRecord(Migration $mig): void
    {
        $existed = $this->tableExists;
        $this->tableExists = $this->migrationTableExists();
        if (!$existed && $this->tableExists) {
            $this->saveMigrationsCache();
        }
        $data = [
            "migration_from" => $mig->getMigratingFrom() ?: "0",
            "migration" => $mig->getMigration(),
            "time" => $mig->getTime() ?: 0,
            "result" => $mig->getResult() ? Migration::RESULT_OK : Migration::RESULT_ERROR
        ];
        if (!$this->tableExists) {
            $this->migrationsCache[] = $data;
        } else {
            $this->teamDatabase->table(Migration::TABLE)->insert($data);
        }
    }

    private function saveMigrationsCache(): void
    {
        if (empty($this->migrationsCache)) {
            return;
        }
        foreach ($this->migrationsCache as $data) {
            $this->teamDatabase->table(Migration::TABLE)->insert($data);
        }
        $this->migrationsCache = [];
    }

    /**
     * Drop all comments from file and get just array of sql queries
     *
     * @throws Exception When there are no commands
     * @return string[]
     */
    public function getCommands(string $contents): array
    {
        $this->logg("Extracting commands from contents");

        $withoutComments = $this->removeComments($contents);
        $withoutRemarks = $this->removeRemarks($withoutComments);
        if (substr_count($withoutRemarks, ";") == 0) {
            throw new Exception("No SQL command detected - are commands correctly ended by semicolon? (;)");
        }

        return array_map('trim', $this->splitSqlFile($withoutRemarks, ';'));
    }

    /**
     * Remove all comments from input
     */
    private function removeComments(string $contents): ?string
    {
        $this->logg("Removing comments");

        $search = '/\/\*.*?\*\//ms';
        $replace = "";

        while (preg_match($search, $contents)) {
            $contents = preg_replace($search, $replace, $contents);
        }

        return $contents;
    }

    /**
     * Strip the sql comment lines out of an uploaded sql file
     */
    private function removeRemarks(string $sql): string
    {
        $this->logg("Removing remarks");
        $lines = explode("\n", $sql);

        $output = [];
        $i = 0;
        foreach ($lines as $line) {
            $i++;
            $lineT = trim($line);
            $lineT = str_replace('&nbsp;', ' ', $lineT);
            if ($lineT === '') {
                continue;
            }
            if ($lineT[0] == "#") {
                continue;
            }
            if (substr($lineT, 0, 2) == "--") {
                continue;
            }

            $output[] = $lineT;
        }

        return implode(" ", $output);
    }

    /**
     * Split an uploaded sql file into single sql statements.
     * Note: expects trim() to have already been run on $sql.
     * @return string[]
     */
    private function splitSqlFile(string $sql, string $delimiter): array
    {
        $this->logg("Splitting contents to SQL commands");
        // Split up our string into "possible" SQL statements.
        //temporary replace escaped semicolons
        $sql = str_replace('\;', "_||_", $sql);
        $tokens = explode($delimiter, $sql);

        foreach ($tokens as &$token) {
            $token = str_replace("_||_", ';', $token);
        }

        // try to save mem.
        $sql = "";
        $output = [];

        // we don't actually care about the matches preg gives us.
        $matches = [];

        // this is faster than calling count($oktens) every time thru the loop.
        $token_count = is_countable($tokens) ? count($tokens) : 0;
        for ($i = 0; $i < $token_count; $i++) {
            // Don't wanna add an empty string as the last thing in the array.
            if (($i !== $token_count - 1) || (strlen($tokens[$i] > 0))) {
                // This is the total number of single quotes in the token.
                $total_quotes = preg_match_all("/'/", $tokens[$i], $matches);
                // Counts single quotes that are preceded by an odd number of backslashes,
                // which means they're escaped quotes.
                $escaped_quotes = preg_match_all("/(?<!\\\\)(\\\\\\\\)*\\\\'/", $tokens[$i], $matches);

                $unescaped_quotes = $total_quotes - $escaped_quotes;

                // If the number of unescaped quotes is even, then the delimiter did NOT occur inside a string literal.
                if (($unescaped_quotes % 2) == 0) {
                    // It's a complete sql statement.
                    $output[] = $tokens[$i];
                    // save memory.
                    $tokens[$i] = "";
                } else {
                    // incomplete sql statement. keep adding tokens until we have a complete one.
                    // $temp will hold what we have so far.
                    $temp = $tokens[$i] . $delimiter;
                    // save memory..
                    $tokens[$i] = "";

                    // Do we have a complete statement yet?
                    $complete_stmt = false;

                    for ($j = $i + 1; (!$complete_stmt && ($j < $token_count)); $j++) {
                        // This is the total number of single quotes in the token.
                        $total_quotes = preg_match_all("/'/", $tokens[$j], $matches);
                        // Counts single quotes that are preceded by an odd number of backslashes,
                        // which means they're escaped quotes.
                        $escaped_quotes = preg_match_all("/(?<!\\\\)(\\\\\\\\)*\\\\'/", $tokens[$j], $matches);

                        $unescaped_quotes = $total_quotes - $escaped_quotes;

                        if (($unescaped_quotes % 2) == 1) {
                            // odd number of unescaped quotes. In combination with the previous incomplete
                            // statement(s), we now have a complete statement. (2 odds always make an even)
                            $output[] = $temp . $tokens[$j];

                            // save memory.
                            $tokens[$j] = "";
                            $temp = "";

                            // exit the loop.
                            $complete_stmt = true;
                            // make sure the outer loop continues at the right point.
                            $i = $j;
                        } else {
                            // even number of unescaped quotes. We still don't have a complete statement.
                            // (1 odd and 1 even always make an odd)
                            $temp .= $tokens[$j] . $delimiter;
                            // save memory.
                            $tokens[$j] = "";
                        }
                    } // for..
                } // else
            }
        }

        return $output;
    }

    /**
     * Log string using defined logger or to internal log array
     *
     * @param string $text
     * @return void
     */
    private function logg(string $text): void
    {
        if (isset($this->logger)) {
            ($this->logger)($text);
        } else {
            $this->log[] = (new DateTime())->format(BaseModel::DATETIME_CZECH_FORMAT) . " " . $text;
        }
    }

    /** @return Migration[] Migrations to perform. First migration might be base migration */
    private function getAllMigrations(?string $latestMigration): array
    {
        $schemaDir = __DIR__ . "/../migrations";
        $glob = glob($schemaDir . "/*.sql");
        $latestDt = $latestMigration ? DateTime::createFromFormat("Y-m-d?H-i-s", $latestMigration) : null;
        $migrationsPerformed = $this->getPerformedSuccesfullMigrations();

        //get only the latest found base and all migrations afterwards
        $migrations = [];
        foreach ($glob as $file) {
            if (preg_match('/.*\/migrations\/' . self::REGEX_BASE . '/', $file)) {  //this migration is a base. Check if current version is before this base or after
                if (empty($latestMigration)) {    //if there is no base migration done yet, use this base as migration base and remove all previous migrations
                    $migrations = [new Migration($file)];
                }
            } elseif (preg_match('/.*\/migrations\/' . self::REGEX_MIGRATION . '/', $file)) {
                $mig = new Migration($file);
                if (in_array($mig->getMigration(), $migrationsPerformed)) {
                    continue;   //skip already performed migration
                }

                //migration to run
                if ($latestDt && $mig->getDatetime() < $latestDt) {   //past, unperformed migration, which can be harmful
                    $this->logg("WARNING: Detected previously skipped migration " . $mig->getMigration() . ", migrating it now, but can cause problems due to impromer migration flow.");
                    $mig->setPastMigration(true);
                }

                $migrations[] = $mig;
            }
        }

        return $migrations;
    }

    /**
     * Migrate database to latest version
     * @return array<string, mixed[]>
     */
    public function migrateUp(): array
    {
        $this->migrateFromVersion1();
        $currentMigration = $this->getLatestMigration();

        $this->logg("Current database version is " . ($currentMigration ?: "not-set"));

        $migrations = $this->getAllMigrations($currentMigration);

        if (empty($migrations)) {
            $this->logg("No migrations found. Database is up to date. Terminating");
            return ["success" => true, "log" => $this->log];
        }

        $latestMigration = end($migrations);
        $this->logg("Latest available migration detected is " . $latestMigration->getMigration());

        $this->logg("Starting migrating, with " . count($migrations) . " pending migrations");
        $success = $this->migrateBatch($migrations);

        if (!$success) {
            $this->logg("ERROR: Migration process finished with errors - please resolve conflicts, according to this log");
        } else {
            $this->logg("Migration process finished succesfully");
        }

        return ["success" => $success, "log" => $this->log];
    }

    /**
     * Function automatically checks whether migrations from v1 has already been performed and if not, performs them before it even starts migrating
     *
     * Can be removed after all teams has been switched to new version
     */
    private function migrateFromVersion1(): void
    {
        //is there some migration table at all?
        $tables = $this->teamDatabase->query("SHOW TABLES")->fetchPairs();

        if (empty($tables)) {
            return; //no tables exists - this is a new system, simply perform the base migration to fill it
        }

        $migrationTableExists = in_array("migration", $tables);
        if ($migrationTableExists) {
            if ($this->teamDatabase->table("migration")->where("result", "OK")->count("id") == 0) {   //migration table exists, but not containing any migration, so its some bloat, remove the table and continue
                $this->teamDatabase->query("DROP TABLE IF EXISTS `migration`;")->fetch();
                $migrationTableExists = false;
            } else {
                return; //there is already migration table structure, no neeed to continue
            }
        }

        $userTable = in_array("users", $tables) ? "users" : (in_array("user", $tables) ? "user" : null);
        if ($userTable) {
            $usersColumns = $this->teamDatabase->query("SHOW COLUMNS FROM $userTable")->fetchPairs();
            $usersHasBirthcodeField = array_key_exists("birth_code", $usersColumns);
        } else {
            $usersHasBirthcodeField = false;
        }

        if (!$usersHasBirthcodeField) {  //this database does not have birth_code field in users
            $this->executeSqlContents(file_get_contents("/var/www/vhosts/tymy.cz/src/v1/1.1.23/sql/0018_birthcode.sql"), $this->log);
        }
        $this->teamDatabase->query("CREATE TABLE `migration` (
                `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
                `created` timestamp NOT NULL DEFAULT current_timestamp(),
                `migration_from` varchar(19) NOT NULL,
                `migration` varchar(19) NOT NULL,
                `time` double NOT NULL,
                `result` enum('OK','ERROR') NOT NULL
              ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
        $this->teamDatabase->table("migration")->insert([
            "migration_from" => "0",
            "migration" => "2021-10-25T11-00-00",
            "time" => "0",
            "result" => "OK",
        ]);
    }

    /**
     * @todo when migrations DOWN are enabled
     */
    public function migrateDown(): void
    {
        $this->logg("Migration DOWN started");
    }

    private function migrateBatch(array $migrations): bool
    {
        Debugger::timer("migration");
        $ok = true;
        $mig = null;

        try {
            foreach ($migrations as $mig) {
                $this->migrateOne($mig);
            }
        } catch (Exception $exc) {
            $msg = "An ERROR happened occured migration: [" . $exc->getMessage() . "], performing rollback.";
            Debugger::log($msg);
            $this->logg($msg);
            $ok = false;
            $this->saveMigrationRecord($mig);
        }

        if (!$ok) {
            return false;
        }

        $this->teamDatabase->getStructure()->rebuild();

        $this->saveMigrationsCache();
        $this->logg("Database migrated in " . Debugger::timer("migration") * 1000 . " ms");
        return true;
    }

    /**
     * Migrate one migration file
     *
     * @param bool $direction True for UP
     */
    private function migrateOne(Migration $mig, bool $direction = self::MIGRATION_UP): void
    {
        $this->logg("Running migration from file " . $mig->getFile());
        $mig->setMigratingFrom($this->getLatestMigration());
        $timer = "migration_" . $mig->getMigration();
        Debugger::timer($timer);
        $this->logg("Running migration " . $mig->getMigration());

        //run the migration commands
        $migrationContents = $direction == self::MIGRATION_UP ? $mig->getUpContents() : $mig->getDownContents();
        $this->executeSqlContents($migrationContents, $this->log);

        $time = Debugger::timer($timer) * 1000;
        $mig->setTime($time);
        $mig->setResult(true);
        $this->logg("Migration " . $mig->getMigration() . " finished in $time ms");
        $this->saveMigrationRecord($mig);
    }
}
