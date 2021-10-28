<?php

namespace Tymy\Module\Admin\Manager;

use Exception;
use Nette\Database\Explorer;
use Nette\Utils\DateTime;
use PDOException;
use Tracy\Debugger;
use Tymy\Module\Admin\Entity\Migration;
use Tymy\Module\Core\Model\BaseModel;


/**
 * Description of MigrationManager
 *
 * @author Matěj Kmínek
 */
class MigrationManager
{

    const MIGRATION_UP = true;
    const MIGRATION_DOWN = false;
    const REGEX_MIGRATION = "\d{4}-\d{2}-\d{2}T\d{2}-\d{2}-\d{2}.sql";
    const REGEX_BASE = "\d{4}-\d{2}-\d{2}T\d{2}-\d{2}-\d{2}-base.sql";

    private Explorer $teamDatabase;
    private array $log = [];
    private bool $tableExists;
    private array $migrationsCache = [];
    
    public function __construct(Explorer $teamDatabase)
    {
        $this->teamDatabase = $teamDatabase;
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
    public function getPerformedSuccesfullMigrations()
    {
        return $this->tableExists ? $this->teamDatabase->table(Migration::TABLE)->where("result", Migration::RESULT_OK)->fetchPairs(null, "migration") : $this->migrationsCache;
    }

    /**
     * Detect in database latest performed migration and return its number, or null if not found
     * @return string|null
     */
    public function getLatestMigration(): ?string
    {
        if (!$this->migrationTableExists()) {
            return null;
        }

        return $this->teamDatabase->table(Migration::TABLE)->where("result", Migration::RESULT_OK)->order("migration DESC")->limit(1)->fetchField("migration");
    }

    public function executeSqlContents($contents, &$log = false)
    {
        if ($log) {
            $this->log = &$log;
        }
        $commands = $this->getCommands($contents);
        return $this->executeCommands($commands);
    }

    public function executeCommands(array $sqlCommands): array
    {
        $this->logg("Executing supplied " . count($sqlCommands) . " queries");
        foreach ($sqlCommands as $cmd) {
            $cmd = trim($cmd);
            if (substr($cmd, 0, -1) !== ";") {    //add semicolon to the end if its not there
                $cmd = $cmd . ";";
            }
            $this->logg("Executing query " . $cmd);
            $this->teamDatabase->query($cmd);
        }
        return $this->log;
    }

    public function saveMigrationRecord(Migration $mig)
    {
        $existed = $this->tableExists ? true : false;
        $this->tableExists = $this->migrationTableExists();
        if (!$existed && $this->tableExists) {
            $this->saveMigrationsCache();
        }
        $data = [
            "migration_from" => $mig->getMigratingFrom(),
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

    private function saveMigrationsCache()
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
     * @param string $contents
     * @return array
     * @throws Exception When there are no commands
     */
    public function getCommands(string $contents): array
    {
        $this->logg("Extracting commands from contents");

        $withoutComments = $this->remove_comments($contents);
        $withoutRemarks = $this->remove_remarks($withoutComments);
        if (substr_count($withoutRemarks, ";") == 0) {
            throw new Exception("No SQL command detected - are commands correctly ended by semicolon? (;)");
        }

        return array_map('trim', $this->split_sql_file($withoutRemarks, ';'));
    }

    private function remove_comments(&$output)
    {
        $this->logg("Removing comments");

        $search = '/\/\*.*?\*\//ms';
        $replace = "";

        while (preg_match($search, $output)) {
            $output = preg_replace($search, $replace, $output);
        }

        return $output;
    }

//
    // remove_remarks will strip the sql comment lines out of an uploaded sql file
//
    private function remove_remarks($sql)
    {
        $this->logg("Removing remarks");
        $lines = explode("\n", $sql);

        $output = [];
        $i = 0;
        foreach ($lines as $line) {
            $i++;
            $lineT = trim($line);
            $lineT = str_replace('&nbsp;', ' ', $lineT);
            if (!strlen($lineT)) {
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

        return join(" ", $output);
    }

//
    // split_sql_file will split an uploaded sql file into single sql statements.
    // Note: expects trim() to have already been run on $sql.
//
    private function split_sql_file($sql, $delimiter)
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
        $token_count = count($tokens);
        for ($i = 0; $i < $token_count; $i++) {
            // Don't wanna add an empty string as the last thing in the array.
            if (($i != ($token_count - 1)) || (strlen($tokens[$i] > 0))) {
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

    private function logg($text)
    {
        $this->log[] = (new DateTime())->format(BaseModel::DATETIME_CZECH_FORMAT) . " " . $text;
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
        $base = null;
        foreach ($glob as $file) {
            if (preg_match('/.*\/migrations\/' . self::REGEX_BASE . '/', $file)) {  //this migration is a base. Check if current version is before this base or after
                if (empty($latestMigration)) {    //if there is no base migration done yet, use this base as migration base and remove all previous migrations
                    $migrations = [new Migration($file)];
                }
            } else if (preg_match('/.*\/migrations\/' . self::REGEX_MIGRATION . '/', $file)) {
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
     */
    public function migrateUp(): array
    {
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
     * @todo when migrations DOWN are enabled
     */
    public function migrateDown()
    {
        $this->logg("Migration DOWN started");
    }

    private function migrateBatch(array $migrations)
    {
        $this->teamDatabase->beginTransaction();
        Debugger::timer("migration");
        $ok = true;
        $mig = null;

        try {
            foreach ($migrations as $mig) {
                $this->migrateOne($mig);
            }
        } catch (Exception $exc) {
            $msg = "An ERROR happened during migration: [" . $exc->getMessage() . "], performing rollback.";
            Debugger::log($msg);
            $this->logg($msg);
            try {
                $this->teamDatabase->rollBack();
            } catch (PDOException $exc) {
                if ($exc->getMessage() !== "There is no active transaction") {//avoid throwing errors on autocommit mode or when someone already commits the transaction
                    throw $exc;
                }
            }
            $ok = false;
            $this->saveMigrationRecord($mig);
        }

        if (!$ok) {
            return false;
        }

        try {
            $this->teamDatabase->commit();
        } catch (PDOException $exc) {
            if ($exc->getMessage() !== "There is no active transaction") {//avoid throwing errors on autocommit mode or when someone already commits the transaction
                throw $exc;
            }
        }

        $this->logg("Database migrated in " . Debugger::timer("migration") * 1000 . " ms");
        return true;
    }

    /**
     * Migrate one migration file
     *
     * @param Migration $mig
     * @param type $direction
     * @return void
     */
    private function migrateOne(Migration $mig, $direction = self::MIGRATION_UP): void
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