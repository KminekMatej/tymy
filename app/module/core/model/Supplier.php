<?php

namespace Tymy\Module\Core\Model;

use stdClass;
use const TEAM_DIR;

class Supplier
{
    private $tym;
    private $versions;
    private $wwwDir;
    private $allSkins;

    public function __construct($appConfig)
    {
        $this->setVersion();
        $this->setAllSkins($appConfig["allSkins"]);
    }

    public function getAppDir()
    {
        return $this->appDir;
    }

    public function setWwwDir($wwwDir)
    {
        $this->wwwDir = $wwwDir;
        return $this;
    }

    public function setAppDir($appDir)
    {
        $this->appDir = $appDir;
        return $this;
    }

    public function getTym()
    {
        return $this->tym;
    }

    public function setTym($tym)
    {
        $this->tym = getenv("AUTOTEST") ? "autotest" : explode(".", $_SERVER["HTTP_HOST"])[0];
        return $this;
    }

    public function getVersions()
    {
        return $this->versions;
    }

    public function getVersion($index = 0)
    {
        return $this->versions[$index];
    }

    public function getVersionCode()
    {
        return $this->getVersion()->version;
    }

    public function setVersion()
    {
        $taglog = file(TEAM_DIR . "/app/tag.log");
        foreach ($taglog as $log) {
            $matches = [];
            preg_match("/([0-9]{4})-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])\s([0-1][0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])\s\+\d{4}\|(\d*)\.(\d*)\.(\d*)/", $log, $matches);
            if (count($matches)) {
                $version = new stdClass();
                $version->year = (int) $matches[1];
                $version->month = (int) $matches[2];
                $version->day = (int) $matches[3];
                $version->hour = (int) $matches[4];
                $version->minute = (int) $matches[5];
                $version->second = (int) $matches[6];
                $version->major = (int) $matches[7];
                $version->minor = (int) $matches[8];
                $version->patch = (int) $matches[9];
                $version->version = $matches[7] . "." . $matches[8] . "." . $matches[9];
                $version->date = date("c", strtotime($matches[1] . "-" . $matches[2] . "-" . $matches[3] . " " . $matches[4] . ":" . $matches[5] . ":" . $matches[6]));
                $this->versions[] = $version;
            }
        }
    }

    public function getAllSkins()
    {
        return $this->allSkins;
    }

    public function setAllSkins($allSkins)
    {
        $this->allSkins = $allSkins;
        return $this;
    }
}
