<?php

namespace Tymy\Module\Core\Model;

use Nette\Utils\DateTime;

/**
 * Simple object to store Version data
 *
 * @author kminekmatej, 21. 2. 2022, 12:14:02
 */
class Version
{
    private string $name;
    private string $major;
    private string $minor;
    private string $patch;
    private DateTime $datetime;

    public function __construct(string $name, ?DateTime $datetime)
    {
        $this->name = $name;
        $this->datetime = $datetime ?: new DateTime();
        $vData = explode(".", $name);
        if (count($vData) === 3 && is_numeric($vData[0]) && is_numeric($vData[1]) && is_numeric($vData[2])) {
            $this->major = (int) $vData[0];
            $this->minor = (int) $vData[1];
            $this->patch = (int) $vData[2];
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
