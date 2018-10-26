<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Model;

class Supplier {
    
    const AUTODETECT = "_autodetect";

    private $tapi_config;
    private $tym;
    private $tymyRoot;
    private $apiRoot;
    private $roleClasses;
    private $statusClasses;
    private $eventColors;
    private $versions;
    private $wwwDir;
    private $appDir;
    

    public function __construct($tapi_config, $wwwDir, $appDir) {
        $this->setTapi_config($tapi_config);
        $this->setWwwDir($wwwDir);
        $this->setAppDir($appDir);
        $this->setVersion();
    }

    public function getTapi_config() {
        return $this->tapi_config;
    }

    public function setTapi_config($tapi_config) {
        $this->tapi_config = $tapi_config;
        $this->setTym($tapi_config['tym']);
        $this->setTymyRoot($tapi_config["protocol"] . "://" . $this->getTym() . "." . $tapi_config["root"]);
        $this->setApiRoot($this->getTymyRoot() . DIRECTORY_SEPARATOR . $tapi_config["tapi_api_root"]);
        $this->setRoleClasses($tapi_config['roles_classes']);
        $this->setStatusClasses($tapi_config['status_classes']);
        $this->setEventColors($tapi_config['event_colors']);
        return $this;
    }

    public function getWwwDir() {
        return $this->wwwDir;
    }

    public function getAppDir() {
        return $this->appDir;
    }

    public function setWwwDir($wwwDir) {
        $this->wwwDir = $wwwDir;
        return $this;
    }

    public function setAppDir($appDir) {
        $this->appDir = $appDir;
        return $this;
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

    public function setTym($tym) {
        $this->tym = $tym == self::AUTODETECT ? explode(".", $_SERVER["HTTP_HOST"])[0] : $tym;
        return $this;
    }

    private function setApiRoot($apiRoot) {
        $this->apiRoot = $apiRoot;
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
        return array_key_exists($status, $this->statusClasses) ? $this->statusClasses[$status] : "primary";
    }

    public function setStatusClasses($statusClasses) {
        $this->statusClasses = $statusClasses;
        return $this;
    }

    public function getEventColors() {
        return $this->eventColors;
    }

    public function setEventColors($eventColors) {
        $this->eventColors = $eventColors;
        return $this;
    }
    
    public function getVersions() {
        return $this->versions;
    }

    public function getVersion($index = 0) {
        return $this->versions[$index];
    }

    public function setVersion() {
        $taglog = file(__DIR__ . "/../tag.log");
        foreach ($taglog as $log) {
            $matches = [];
            preg_match("/([0-9]{4})-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])\s([0-1][0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])\s\+\d{4}\|(\d*)\.(\d*)\.(\d*)/", $log, $matches);
            if (count($matches)) {
                $version = new \stdClass();
                $version->year = (int)$matches[1];
                $version->month = (int)$matches[2];
                $version->day = (int)$matches[3];
                $version->hour = (int)$matches[4];
                $version->minute = (int)$matches[5];
                $version->second = (int)$matches[6];
                $version->major = (int)$matches[7];
                $version->minor = (int)$matches[8];
                $version->patch = (int)$matches[9];
                $version->version = $matches[7] . "." . $matches[8] . "." . $matches[9];
                $version->date = date("c", strtotime($matches[1] . "-" . $matches[2] . "-" . $matches[3] . " " . $matches[4] . ":" . $matches[5] . ":" . $matches[6]));                
                $this->versions[] = $version;
            }
        }
    }
    
    public function isHttps(){
        return strtolower($this->getTapi_config()["protocol"]) == "https";
    }
    



}
