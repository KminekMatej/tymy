<?php

namespace Tymy\Module\Core\Model;

use stdClass;
use const TEAM_DIR;

class Supplier
{
    private $allSkins;

    public function __construct($appConfig)
    {
        $this->setAllSkins($appConfig["allSkins"]);
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
