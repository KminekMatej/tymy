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

    private string $migration;

    /** @var mixed|null */
    private $migratingFrom;

    private \Nette\Utils\DateTime $datetime;

    private string $fileContents;

    private string $upContents;

    private string $downContents;

    /** @var mixed|null */
    private $time;

    private bool $result = false;

    private bool $pastMigration = false;

    /**
     * @param string $file
     */
    public function __construct(private $file)
    {
        $migFileInfo = pathinfo($file);
        $this->migration = str_replace("base-", "", $migFileInfo["filename"]); //remove base prefix, if exists
        $this->datetime = DateTime::createFromFormat("Y-m-d?H-i-s", $this->migration);

        $this->fileContents = @fread(@fopen($file, 'r'), @filesize($file));

        $migSections = explode("-- DOWN:", $this->fileContents);

        $this->upContents = $migSections[0];
        $this->downContents = $migSections[1];
    }

    public function getFile(): string
    {
        return $this->file;
    }

    public function getMigration(): string
    {
        return $this->migration;
    }

    public function getDatetime(): DateTime
    {
        return $this->datetime;
    }

    public function getFileContents(): string
    {
        return $this->fileContents;
    }

    public function getUpContents(): string
    {
        return $this->upContents;
    }

    public function getDownContents(): string
    {
        return $this->downContents;
    }

    public function getTime(): float
    {
        return $this->time;
    }

    public function getResult(): bool
    {
        return $this->result;
    }

    public function setTime(float $time): static
    {
        $this->time = $time;
        return $this;
    }

    public function setResult(bool $result): static
    {
        $this->result = $result;
        return $this;
    }

    public function getMigratingFrom(): string
    {
        return $this->migratingFrom;
    }

    public function setMigratingFrom(string $migratingFrom): static
    {
        $this->migratingFrom = $migratingFrom;
        return $this;
    }

    public function isPastMigration(): bool
    {
        return $this->pastMigration;
    }

    public function setPastMigration(bool $pastMigration): static
    {
        $this->pastMigration = $pastMigration;
        return $this;
    }
}
