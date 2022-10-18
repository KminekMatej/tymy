<?php

// phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses

namespace Tymy\Module\Core\Exception;

use Nette\Application\BadRequestException;
use PDOException;
use Tracy\Debugger;
use Tracy\ILogger;

/**
 * Description of DBException
 *
 * @author kminekmatej, 01.09.2020
 */
class DBException
{
    public const TYPE_DELETE = "delete";
    public const TYPE_UPDATE = "update";

    public static function from(PDOException $exc, $type = null)
    {
        switch ($exc->getCode()) {
            case 23000: // key integrity failure
                $matches = [];
                $re = '/.* constraint fails \((.*), CONSTRAINT (.*) FOREIGN KEY \((.*)\) REFERENCES (.*) \((.*)\).*\)/';
                preg_match($re, $exc->getMessage(), $matches);
                if (count($matches) > 0) {
                    return match ($type) {
                        self::TYPE_DELETE => new DeleteIntegrityException($matches[3], $matches[4], $matches[5], $matches[1], $matches[2]),
                        self::TYPE_UPDATE => new UpdateIntegrityException($matches[3], $matches[4], $matches[5], $matches[1], $matches[2]),
                        default => new IntegrityException($matches[3], $matches[4], $matches[5], $matches[1], $matches[2]),
                    };
                }
                $re = '/Column \'(.*)\' cannot be null/m';
                preg_match($re, $exc->getMessage(), $matches);
                if (count($matches) > 0) {
                    $msg = "Property '" . $matches[1] . "' cannot be null";
                    throw new BadRequestException($msg, 400, $exc);
                }

                // no break
            default:
                throw $exc;
        }
    }
}

class IntegrityException extends \Exception
{
    /** @var string */
    public $failingField;

    /** @var string */
    public $relatedTable;

    /** @var string */
    public $relatedColumn;

    /** @var string */
    public $fkTable;

    /** @var string */
    public $fkName;

    public function __construct($failingField, $relatedTable, $relatedColumn, $fkTable, $fkName)
    {
        $this->failingField = $this->backslashtrim($failingField);
        $this->relatedTable = $this->backslashtrim($relatedTable);
        $this->relatedColumn = $this->backslashtrim($relatedColumn);
        $this->fkTable = $this->backslashtrim($fkTable);
        $this->fkName = $this->backslashtrim($fkName);
        $msg = "Related record, specified by field $failingField does not exist in column $relatedTable.$relatedColumn. (constraint $fkTable:$fkName)";
        Debugger::log($msg, ILogger::ERROR);
        parent::__construct($msg);
    }

    private function backslashtrim($str)
    {
        $tblData = explode("`.`", $str);
        return count($tblData) == 1 ? trim($str, "`") : trim($tblData[1], "`");
    }
}

class DeleteIntegrityException extends IntegrityException
{
    public $blockingIds = [];

    public function withIds($blockingIds): static
    {
        $this->blockingIds = $blockingIds;
        return $this;
    }
}

class UpdateIntegrityException extends IntegrityException
{
    public array $blockingIds = [];

    public static function withIds($failingField, $relatedTable, $relatedColumn, $fkTable, $fkName, array $blockingIds): \Tymy\Module\Core\Exception\UpdateIntegrityException
    {
        $e = new UpdateIntegrityException($failingField, $relatedTable, $relatedColumn, $fkTable, $fkName);
        $e->blockingIds = $blockingIds;
        return $e;
    }
}

class NotFoundException extends \Exception
{
    /**
     * @param string $failingField
     * @param string $relatedTable
     * @param string $relatedColumn
     * @param string $fkTable
     * @param string $fkName
     */
    public function __construct(public $failingField, public $relatedTable, public $relatedColumn, public $fkTable, public $fkName)
    {
        parent::__construct("Related record, specified by field $failingField does not exist in column $relatedTable.$relatedColumn. (constraint $fkTable:$fkName)");
    }
}
