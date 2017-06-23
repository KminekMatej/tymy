<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Model;

class Supplier {
    
    const HTTP = "http://"; // could be changed to https in future?
    const TYMY_ROOT = "tymy.cz";
    const TAPI_API_FOLD = "api";
    const TAPI_SYSAPI_FOLD = "sysapi";
    const URL_SEPARATOR = "/";
    
    private $tym;
    private $tymyRoot;
    private $apiRoot;
    private $sysapiRoot;
    
    public function __construct($tym) {
        $this->setTym($tym);
        $this->setTymyRoot(self::HTTP . $tym . "." . self::TYMY_ROOT);
        $this->setApiRoot($this->getTymyRoot() . self::URL_SEPARATOR . self::TAPI_API_FOLD);
        $this->setSysapiRoot($this->getTymyRoot() . self::URL_SEPARATOR . self::TAPI_SYSAPI_FOLD);
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



}
