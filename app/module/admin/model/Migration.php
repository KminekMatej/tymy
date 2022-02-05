<?php

namespace Tymy\Module\Admin\Entity;

use Nette\Utils\DateTime;

/**
 * Description of Migration
 *
 * @author Matěj Kmínek
 */
class Migration
{
    public const TABLE = "migration";
    public const RESULT_OK = "OK";
    public const RESULT_ERROR = "ERROR";

    /** @var string */
    private $file;

    /** @var string */
    private $migration;

    /** @var string */
    private $migratingFrom;

    /** @var DateTime */
    private $datetime;

    /** @var string */
    private $fileContents;

    /** @var string */
    private $upContents;

    /** @var string */
    private $downContents;

    /** @var double */
    private $time;

    /** @var bool */
    private $result = false;

    /** @var bool */
    private $pastMigration = false;

    public function __construct($file)
    {
        $this->file = $file;

        $migFileInfo = pathinfo($file);
        $this->migration = str_replace("base-", "", $migFileInfo["filename"]); //remove base prefix, if exists
        $this->datetime = DateTime::createFromFormat("Y-m-d?H-i-s", $this->migration);

        $this->fileContents = @fread(@fopen($file, 'r'), @filesize($file));

        $migSections = explode("-- DOWN:", $this->fileContents);

        $this->upContents = $migSections[0];
        $this->downContents = $migSections[1];
    }

    public function getFile()
    {
        return $this->file;
    }

    public function getMigration()
    {
        return $this->migration;
    }

    public function getDatetime(): DateTime
    {
        return $this->datetime;
    }

    public function getFileContents()
    {
        return $this->fileContents;
    }

    public function getUpContents()
    {
        return $this->upContents;
    }

    public function getDownContents()
    {
        return $this->downContents;
    }

    public function getTime()
    {
        return $this->time;
    }

    public function getResult()
    {
        return $this->result;
    }

    public function setTime($time)
    {
        $this->time = $time;
        return $this;
    }

    public function setResult($result)
    {
        $this->result = $result;
        return $this;
    }

    public function getMigratingFrom()
    {
        return $this->migratingFrom;
    }

    public function setMigratingFrom($migratingFrom)
    {
        $this->migratingFrom = $migratingFrom;
        return $this;
    }

    public function isPastMigration()
    {
        return $this->pastMigration;
    }

    public function setPastMigration($pastMigration)
    {
        $this->pastMigration = $pastMigration;
        return $this;
    }
}
