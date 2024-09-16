<?php

namespace Tymy\Module\Core\Model;

use Nette\Utils\DateTime;

/**
 * Simple object to store Version data
 */
class Version
{
    private string $major;
    private string $minor;
    private string $patch;
    private DateTime $datetime;

    public function __construct(private string $name, ?DateTime $datetime)
    {
        $this->datetime = $datetime ?: new DateTime();
        $vData = explode(".", $name);
        if (count($vData) === 3 && is_numeric($vData[0]) && is_numeric($vData[1]) && is_numeric($vData[2])) {
            $this->major = $vData[0];
            $this->minor = $vData[1];
            $this->patch = $vData[2];
        } else {
            $this->major = $name;
            $this->minor = "0";
            $this->patch = "0";
        }
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getMajor(): string
    {
        return $this->major;
    }

    public function getMinor(): string
    {
        return $this->minor;
    }

    public function getPatch(): string
    {
        return $this->patch;
    }

    public function getDatetime(): DateTime
    {
        return $this->datetime;
    }
}
