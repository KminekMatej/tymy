<?php

// phpcs:disable PSR1.Files.SideEffects

namespace Tymy\Bin;

use Nette\Database\Explorer;
use Nette\DI\Container;
use Nette\Loaders\RobotLoader;
use Nette\Utils\FileSystem;
use Tymy\Bootstrap;
use Tymy\Module\Core\Helper\ArrayHelper;
use Tymy\Module\Core\Helper\StringHelper;
use Tymy\Module\Core\Manager\BaseManager;
use Tymy\Module\Core\Model\Field;

use function count;

use const MODULES_DIR;
use const ROOT_DIR;

require(__DIR__ . "/Common.php");
require(__DIR__ . "/../app/Bootstrap.php");

class StructureChecker
{
    private string $tmpDir;
    private array $modules;
    private bool $fix = false;
    private int $errCount = 0;
    private Container $container;
    private Explorer $database;
    private RobotLoader $robotLoader;

    public function run($args): void
    {
        define("ROOT_DIR", FileSystem::normalizePath(Common::getCwdUnresolved() . "/.."));
        define("TEAM_DIR", ROOT_DIR);
        putenv("team=mk");
        $this->loadArguments($args);
        Common::logg("Checking database structure against mapper configuration");
        $this->container = Bootstrap::boot();
        $this->database = $this->container->getByName("database.team.explorer");
        define("WWW_DIR", ROOT_DIR . "/www");

        $this->mockServer();

        $this->tmpDir = __DIR__ . "/../temp/tymy.cz/mapper-checker";
        Common::logg("Storring results to tmp dir " . $this->tmpDir);
        if (!is_dir($this->tmpDir)) {
            mkdir($this->tmpDir, 0777, true);
        }

        //load all managers
        $managerMapping = $this->getManagerMapping();

        foreach ($managerMapping as $managerMap) {
            $manager = $this->container->getByType($managerMap["managerClassName"]);
            if (!$manager instanceof BaseManager) {
                Common::warnLogg($managerMap["managerClassName"] . " is not an instance of BaseManager, skipping");
                continue;
            }
            if (isset($this->modules) && !in_array($manager->getModule(), $this->modules)) {
                continue;   //skip this module
            }
            $this->processManager($manager);
        }

        if ($this->errCount > 0) {
            Common::warnLogg("Found {$this->errCount} errors in database structure");
            exit(1);
        } else {
            Common::successLogg("No errors in database structure found");
        }
    }

    private function mockServer()
    {
        $_SERVER["SERVER_NAME"] = getenv("SERVER_NAME") ?: gethostname();    //needed in websocket client manager
    }

    private function processManager(BaseManager $manager)
    {
        //get scheme
        //get nette database cache of a table form manager
        //compare columns from NDC against our mappers
        $table = $manager->getTable();

        $columns = $this->database->getStructure()->getColumns($table);
        $fields = $manager->getScheme();

        foreach ($fields as $field) {
            $this->processField($manager, $field, $columns);
        }
    }

    private function processField(BaseManager $manager, Field $field, array $columns)
    {
        //get coresponding column from columns cache
        $column = ArrayHelper::subValue($columns, "name", $field->getColumn());

        if (!$column) {
            Common::errLogg($manager::class . "::getScheme() contains `{$field->getColumn()}` but that column does not exist in database");
            $this->errCount++;
            return;
        }

        $this->fieldType($manager, $field, $column);
        $this->fieldEnum($manager, $field, $column);
    }

    /**
     * Proccess correct type of field
     *
     * @param BaseManager $manager
     * @param array $column As tuples [(string) name, (string) table, (string) nativetype, (int) size, (bool) nullable, (mixed) default, (bool) autoincrement, (bool) primary, (array) vendor]]
     * @return void
     */
    private function fieldType(BaseManager $manager, Field $field, array $column): void
    {
        $managerClass = $manager::class;
        $columnName = $column["name"];
        $columnType = trim(str_replace("/* MARIADB-5.3 */", "", $column["nativetype"])); //replace comment /* MARIADB-5.3 */, sometimes appended to some fields
        $columnSize = $column["size"];

        switch ($field->getType()) {
            case Field::TYPE_INT:
                if (!in_array($columnType, ["INT", "TINYINT", "SMALLINT", "MEDIUMINT", "BIGINT", "BOOLEAN"])) {
                    if ($this->fix) {
                        $this->fixType($manager, $columnName, $column);
                    } else {
                        Common::errLogg("$managerClass::getScheme() specifies `{$field->getColumn()}` as `{$field->getType()}` but in database its `$columnType`");
                        $this->errCount++;
                    }
                }
                break;
            case Field::TYPE_FLOAT:
                if (!in_array($columnType, ["DOUBLE", "FLOAT", "DECIMAL", "REAL"])) {
                    if ($this->fix) {
                        $this->fixType($manager, $columnName, $column);
                    } else {
                        Common::errLogg("$managerClass::getScheme() specifies `{$field->getColumn()}` as `{$field->getType()}` but in database its `$columnType`");
                        $this->errCount++;
                    }
                }
                break;
            case Field::TYPE_STRING:
                if (!in_array($columnType, ["CHAR", "VARCHAR", "TINYTEXT", "MEDIUMTEXT", "LONGTEXT", "TEXT", "ENUM", "SET"])) {
                    if ($this->fix) {
                        $this->fixType($manager, $columnName, $column);
                    } else {
                        Common::errLogg("$managerClass::getScheme() specifies `{$field->getColumn()}` as `{$field->getType()}` but in database its `$columnType`");
                        $this->errCount++;
                    }
                } elseif (($field->getMaxLength() || $columnSize) && $columnSize !== $field->getMaxLength()) {  //always first check the type and only when type fits, continue with checks
                    if ($this->fix) {
                        $this->fixStringLength($manager, $columnName, $columnSize);
                    } else {
                        Common::errLogg("$managerClass::getScheme() specifies `{$field->getColumn()}` size `{$field->getMaxLength()}` but in database its `$columnSize`");
                        $this->errCount++;
                    }
                }
                break;
            case Field::TYPE_DATETIME:
                if (!in_array($columnType, ["TIMESTAMP", "DATETIME"])) {
                    if ($this->fix) {
                        $this->fixType($manager, $columnName, $column);
                    } else {
                        Common::errLogg("$managerClass::getScheme() specifies `{$field->getColumn()}` as `{$field->getType()}` but in database its `$columnType`");
                        $this->errCount++;
                    }
                }
                break;
            case Field::TYPE_DATE:
                if (!in_array($columnType, ["DATE"])) {
                    if ($this->fix) {
                        $this->fixType($manager, $columnName, $column);
                    } else {
                        Common::errLogg("$managerClass::getScheme() specifies `{$field->getColumn()}` as `{$field->getType()}` but in database its `$columnType`");
                        $this->errCount++;
                    }
                }
                break;

            default:
                break;
        }
    }

    /**
     * Proccess correct enumeration of field
     *
     * @param BaseManager $manager
     * @param Field $field
     * @param array $column
     * @return void
     */
    private function fieldEnum(BaseManager $manager, Field $field, array $column): void
    {
        if ($column["nativetype"] !== "ENUM") {
            return;
        }

        $managerClass = $manager::class;
        $columnName = $column["name"];
        $matches = [];
        preg_match('/enum\((.*)\)/m', $column["vendor"]["type"], $matches);
        $enums = explode(",", str_replace("'", "", $matches[1]));

        if (empty($field->getEnum())) {
            if ($this->fix) {
                $this->fixEnum($manager, $columnName, $enums);
            } else {
                Common::errLogg("$managerClass::getScheme() does not contain any ENUM on field `{$field->getColumn()}` but in database its [" . join(", ", $enums) . "]");
                $this->errCount++;
            }
            return;
        }
        $diff = array_merge(
            array_diff($enums, $field->getEnum()),
            array_diff($field->getEnum(), $enums),
        );

        if (!empty($diff)) {
            if ($this->fix) {
                $this->fixEnum($manager, $columnName, $enums);
            } else {
                Common::errLogg("$managerClass::getScheme() specifies ENUM on field `{$field->getColumn()}` as [" . join(", ", $field->getEnum()) . "] but in database its [" . join(", ", $enums) . "]");
                $this->errCount++;
            }
        }
    }

    /**
     * Performs in-line replacement in mapper to fix max length according to new value
     *
     * @param BaseManager $manager
     * @param string $fieldColumn
     * @param int $newLength
     * @return void
     */
    private function fixStringLength(BaseManager $manager, string $fieldColumn, int $newLength): void
    {
        $managerClass = $manager::class;

        //get file path
        $path = $this->getMapperPath($manager);
        if (!$path) {
            Common::errLogg("[$managerClass:$fieldColumn] Mapper of $managerClass not found");
                $this->errCount++;
            return;
        }

        $fileContents = file_get_contents($path);

        $re1 = '/Field::string\((.*)\)(.*)->withPropertyAndColumn\("' . $fieldColumn . '/m';
        $replace1 = 'Field::string(' . $newLength . ')$2->withPropertyAndColumn("' . $fieldColumn;
        $re2 = '/Field::string\((.*)\)(.*)->withColumn\("' . $fieldColumn . '/m';
        $replace2 = 'Field::string(' . $newLength . ')$2->withColumn("' . $fieldColumn;

        if (preg_match($re1, $fileContents)) {
            $newContents = preg_replace($re1, $replace1, $fileContents);
        } elseif (preg_match($re2, $fileContents)) {
            $newContents = preg_replace($re2, $replace2, $fileContents);
        } else {
            Common::errLogg("[$managerClass:$fieldColumn] Not found in mapper file, better check it");
                $this->errCount++;
            return;
        }

        file_put_contents($path, $newContents);
        Common::successLogg("[$managerClass:$fieldColumn] Field length fixed to $newLength");
    }

    /**
     * Attempt to detect path of mapper
     *
     * @param BaseManager $manager
     * @return string|null
     */
    private function getMapperPath(BaseManager $manager): ?string
    {
        if (!isset($this->robotLoader)) {
            $this->robotLoader = new RobotLoader();
            $this->robotLoader->setTempDirectory($this->tmpDir);
            $this->robotLoader->addDirectory(ROOT_DIR . '/app');
            $this->robotLoader->rebuild();
        }

        $managerParts = explode("\\", $manager::class);
        $managerShort = array_pop($managerParts);
        $mapperClass = str_replace("\Manager", "\Mapper", join("\\", $managerParts)) . "\\" . str_replace("Manager", "Mapper", $managerShort);

        return $this->robotLoader->getIndexedClasses()[$mapperClass] ?? null;
    }

    /**
     * Performs in-line replacement in mapper to fix max length according to new value
     *
     * @param BaseManager $manager
     * @param string $fieldColumn
     * @param array $column
     * @return void
     */
    private function fixType(BaseManager $manager, string $fieldColumn, array $column): void
    {
        $columnType = trim(str_replace("/* MARIADB-5.3 */", "", $column["nativetype"])); //replace comment /* MARIADB-5.3 */, sometimes appended to some fields
        $columnSize = $column["size"];

        $managerClass = $manager::class;

        $fieldType = null;
        switch ($columnType) {
            case "TINYINT":
            case "SMALLINT":
            case "MEDIUMINT":
            case "INT":
            case "BIGINT":
            case "BOOLEAN":
                $fieldType = "int";
                $columnSize = null; //in Field mapper, there is no column size parameter
                break;

            case "DECIMAL":
                $fieldType = "float";
                $columnSize = null;
                if (preg_match('/decimal\((\d*),(\d*)\)/m', $column["vendor"]["type"], $matches)) { //parse out decimals from size "5,2"
                    $columnSize = $matches[2];
                }
                break;
            case "FLOAT":
            case "DOUBLE":
            case "REAL":
                $fieldType = "float";
                $columnSize = null; //in Field mapper, there is no column size parameter
                break;

            case "DATE":
                $fieldType = "date";
                $columnSize = null; //in Field mapper, there is no column size parameter
                break;
            case "DATETIME":
            case "TIMESTAMP":
            case "TIME":
            case "YEAR":
                $fieldType = "datetime";
                $columnSize = null; //in Field mapper, there is no column size parameter
                break;

            case "CHAR":
            case "VARCHAR":
            case "TINYTEXT":
            case "TEXT":
            case "MEDIUMTEXT":
            case "LONGTEXT":
            case "ENUM":
            case "SET":
                $fieldType = "string";
                break;
            default:
                Common::errLogg("[$managerClass:$fieldColumn] Unknown column type: $columnType");
                $this->errCount++;
                return;
        }

        //get file path
        $managerParts = explode("\\", $manager::class);
        $mapperName = str_replace("Manager", "Mapper", array_pop($managerParts));
        $module = $manager->getModule();
        $path = MODULES_DIR . "/$module/mapper/$mapperName.php";

        if (!file_exists($path)) {
            Common::errLogg("[$managerClass:$fieldColumn] Mapper file $path not found during our attempt to fix it");
                $this->errCount++;
            return;
        }

        $fileContents = file_get_contents($path);

        $re1 = '/Field::(.*)\(.*\)(.*)->withPropertyAndColumn\("' . $fieldColumn . '/m';
        $replace1 = 'Field::' . $fieldType . '(' . $columnSize . ')$2->withPropertyAndColumn("' . $fieldColumn . '';

        $re2 = '/Field::(.*)\(.*\)(.*)->withColumn\("' . $fieldColumn . '/m';
        $replace2 = 'Field::' . $fieldType . '(' . $columnSize . ')$2->withColumn("' . $fieldColumn . '';

        if (preg_match($re1, $fileContents)) {
            $newContents = preg_replace($re1, $replace1, $fileContents);
        } elseif (preg_match($re2, $fileContents)) {
            $newContents = preg_replace($re2, $replace2, $fileContents);
        } else {
            Common::errLogg("[$managerClass:$fieldColumn] Not found in mapper file, better check it");
                $this->errCount++;
            return;
        }

        file_put_contents($path, $newContents);
        Common::successLogg("[$managerClass:$fieldColumn] Field type fixed to $fieldType" . ($columnSize ? "($columnSize)" : ""));
    }

    /**
     * Performs in-line replacement in mapper to fix max setEnum specification according to database ENUM value
     *
     * @param BaseManager $manager
     * @param string $fieldColumn
     * @param array $enumToSet
     * @return void
     */
    private function fixEnum(BaseManager $manager, string $fieldColumn, array $enumToSet): void
    {
        $managerClass = $manager::class;

        //get file path
        $managerParts = explode("\\", $manager::class);
        $mapperName = str_replace("Manager", "Mapper", array_pop($managerParts));
        $module = $manager->getModule();
        $path = MODULES_DIR . "/$module/mapper/$mapperName.php";

        if (!file_exists($path)) {
            Common::errLogg("[$managerClass:$fieldColumn] Mapper file $path not found during our attempt to fix it");
                $this->errCount++;
            return;
        }

        $fileContents = file_get_contents($path);

        //remove any setEnum on this line, where this field is specified by withPropertyAndColumn
        $re = '/Field::(.*)->withPropertyAndColumn\("' . $fieldColumn . '"(.*?)\)->setEnum\(.*?\)(.*)/m';
        $subst = 'Field::$1->withPropertyAndColumn("' . $fieldColumn . '"$2)$3';
        $newContents = preg_replace($re, $subst, $fileContents);

        //remove any setEnum on this line, where this field is specified by withColumn
        $re = '/Field::(.*)->withColumn\("' . $fieldColumn . '"(.*?)\)->setEnum\(.*?\)(.*)/m';
        $subst = 'Field::$1->withColumn("' . $fieldColumn . '"$2)$3';
        $newContents = preg_replace($re, $subst, $newContents);

        //add new setEnum on this line, where this field is specified by withPropertyAndColumn
        $re = '/Field::(.*)->withPropertyAndColumn\("' . $fieldColumn . '"(.*?)\)(.*)(,)$/m';
        $enumString = "'" . join("', '", $enumToSet) . "'";
        $subst = 'Field::$1->withPropertyAndColumn("' . $fieldColumn . '"$2)$3->setEnum([' . $enumString . ']),';
        $newContents = preg_replace($re, $subst, $newContents);

        //add new setEnum on this line, where this field is specified by withColumn
        $re = '/Field::(.*)->withColumn\("' . $fieldColumn . '"(.*?)\)(.*)(,)$/m';
        $enumString = "'" . join("', '", $enumToSet) . "'";
        $subst = 'Field::$1->withColumn("' . $fieldColumn . '"$2)$3->setEnum([' . $enumString . ']),';
        $newContents = preg_replace($re, $subst, $newContents);

        file_put_contents($path, $newContents);
    }

    /**
     * Initialize manager mapping - either by taking it from cache, or generating it newly
     *
     * @return array arrays with keys "module", "table", "managerClassName", "entityClassName", "isMain"
     */
    private function getManagerMapping(): array
    {
        $map = [];
        //find all mapped managers
        foreach ($this->container->findByType(BaseManager::class) as $managerName) {
            /* @var $manager BaseManager */
            $manager = $this->container->getByName($managerName);
            $classname = $manager->getClassName();
            $module = $manager->getModule();
            $table = $manager->getTable();

            $expectedManagerName = StringHelper::toCamelCase($module, true) . "Manager";  //transform module into expected manager name, for instance transform module 'business-case' into BusinessCaseManager. If that is the currently processed manager, mark it as main

            $isMainManager = $managerName == $expectedManagerName;
            $map[] = [
                "module" => $module,
                "table" => $table,
                "managerClassName" => get_class($manager),
                "entityClassName" => $classname,
                "isMain" => $isMainManager,
            ];
        }

        return $map;
    }

    private function help()
    {
        Common::DUMPLOGO();

        echo "\nTymy.CZ Mapper checker \n"
        . "This script automatically parses tymy.cz application mappers and checks them against database. Prints out errors:\n"
        . "1) If mapper string maxLength differs from column size .\n"
        . "2) If mapper type differs from database type.\n\n"
        . "Usage: php mapper-check.php [parameters]\n\n"
        . "Parameters: \n\n"
        . "-h | --help ....... Prints this help and exits\n"
        . "-f | --fix ........ Attempt to automatically fix found errors.\n"
        . "-m | --module ..... Process only selected modules (modules can be separated using comma)\n";
    }

    /**
     * Load input arguments to corresponding properties
     *
     * @param array $args In-line array of arguments
     * @return void
     */
    private function loadArguments($args): void
    {
        array_shift($args); //drop first parameter (script name)

        if (in_array("--help", $args)) {
            $this->help();
            exit(0);
        }

        while (count($args)) {
            $arg = array_shift($args);
            switch (true) {
                case in_array($arg, ["-v", "--verbose"]):
                    Common::$verboseMode = true;
                    break;
                case in_array($arg, ["-f", "--fix"]):
                    $this->fix = true;
                    break;
                case in_array($arg, ["-m", "--module"]):
                    $this->modules = explode(",", array_shift($args));
                    Common::logg("Processing only modules " . join(", ", $this->modules));
                    break;
                case in_array($arg, ["-h", "--help"]):
                    $this->help();
                    exit(0);
                default:
                    array_shift($args); //drop unknown parameter
            }
        }
    }
}

(new StructureChecker())->run($argv);
