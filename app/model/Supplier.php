<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Model;

class Supplier {

    private $tym;
    private $tymyRoot;
    private $apiRoot;
    private $sysapiRoot;
    private $roleClasses;
    private $statusClasses;
    
    public function __construct($tapi_config) {
        $this->setTym($tapi_config['tym']);
        $this->setTymyRoot($tapi_config["protocol"] . "://" . $tapi_config["tym"] . "." . $tapi_config["root"]);
        $this->setApiRoot($this->getTymyRoot() . DIRECTORY_SEPARATOR . $tapi_config["tapi_api_root"]);
        $this->setSysapiRoot($this->getTymyRoot() . DIRECTORY_SEPARATOR . $tapi_config["tapi_sysapi_root"]);
        $this->setRoleClasses($tapi_config['roles_classes']);
    }
    
    public function getTym() {
        return $this->tym;
    }

    public function getTymyRoot() {
        return $this->tymyRoot;
    }

    public function getApiRoot() {
        return $this->apiRoot;
    }

    public function getSysapiRoot() {
        return $this->sysapiRoot;
    }

    private function setTym($tym) {
        $this->tym = $tym;
        return $this;
    }

    private function setApiRoot($apiRoot) {
        $this->apiRoot = $apiRoot;
        return $this;
    }

    private function setSysapiRoot($sysapiRoot) {
        $this->sysapiRoot = $sysapiRoot;
        return $this;
    }

    private function setTymyRoot($tymyRoot) {
        $this->tymyRoot = $tymyRoot;
        return $this;
    }

    public function getRoleClass($role) {
        return $this->roleClasses[$role];
    }

    public function setRoleClasses($roleClasses) {
        $this->roleClasses = $roleClasses;
        return $this;
    }
    
    public function getStatusClass($status) {
        return $this->statusClasses[$status];
    }

    public function setStatusClasses($statusClasses) {
        $this->statusClasses = $statusClasses;
        return $this;
    }



}
