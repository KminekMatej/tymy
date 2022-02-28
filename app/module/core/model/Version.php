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
        if ($name == "master") {
            $this->major = "?";
            $this->minor = "?";
            $this->patch = "?";
        } else {
            $parts = explode(".", $name);
            $this->major = $parts[0];
            $this->minor = $parts[1];
            $this->patch = $parts[2];
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
