<?php

namespace Tymy\Module\Core\Model;

use Nette\Neon\Neon;
use stdClass;

class Supplier {

    const AUTODETECT = "_autodetect";

    private $tapi_config;
    private $tym;
    private $tymyRoot;
    private $apiRoot;
    private $versions;
    private $wwwDir;
    private $appDir;
    private $allSkins;
    private $teamNeonDir;
    private $teamNeon;
    private $userNeon;

    public function __construct($tapi_config, $wwwDir, $appDir, $appConfig) {
        $this->setTapi_config($tapi_config);
        $this->setWwwDir($wwwDir);
        $this->setAppDir($appDir);
        $this->setVersion();
        $this->setAllSkins($appConfig["allSkins"]);
        $this->setTeamNeonDir(sprintf($tapi_config["src_dir"], $this->getAppDir(), $this->getTym()));
        $this->loadTeamNeon();
    }

    public function getTapi_config() {
        return $this->tapi_config;
    }

    private function loadTeamNeon() {
        $tmpTeamNeon = $this->getAppDir() . "/config/config.team.template.neon";
        $teamNeon = $this->getTeamNeonDir() . "/config.team.neon";
        if (!file_exists($teamNeon) && file_exists($tmpTeamNeon))
            copy($tmpTeamNeon, $teamNeon);
        if (!file_exists($teamNeon))
            return NULL;
        $this->setTeamNeon((object) Neon::decode(file_get_contents($teamNeon)));
    }

    public function loadUserNeon($userId) {
        $tmpUserNeon = $this->getAppDir() . "/config/config.user.template.neon";
        $userNeon = $this->getUserNeonFile($userId);
        if (!file_exists($userNeon) && file_exists($tmpUserNeon))
            copy($tmpUserNeon, $userNeon);
        if (!file_exists($userNeon))
            return NULL;
        $this->setUserNeon((object) Neon::decode(file_get_contents($userNeon)));
    }

    public function saveUserNeon($userId, $neonArray){
        $userNeon = $this->getUserNeonFile($userId);
        file_put_contents($userNeon, Neon::encode($neonArray));
    }
    
    public function saveTeamNeon($neonArray){
        $teamNeon = $this->getTeamNeonFile();
        file_put_contents($teamNeon, Neon::encode($neonArray, Neon::BLOCK));
    }
    
    public function setTapi_config($tapi_config) {
        $this->tapi_config = $tapi_config;
        $this->setTym($tapi_config['tym']);
        $this->setTymyRoot($tapi_config["protocol"] . "://" . $this->getTym() . "." . $tapi_config["root"]);
        $this->setApiRoot($this->getTymyRoot() . DIRECTORY_SEPARATOR . $tapi_config["tapi_api_root"]);
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
        $this->tym = getenv("AUTOTEST") ? "autotest" : explode(".", $_SERVER["HTTP_HOST"])[0];
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
        return $this->getTeamNeon()->roles_classes[$role];
    }

    public function getStatusClass($code) {
        return array_key_exists($code, $this->getTeamNeon()->status_classes) ? $this->getTeamNeon()->status_classes[$code] : "primary";
    }
    
    public function getStatusColor($code) {
        return property_exists($this->getTeamNeon(), "status_colors") && array_key_exists($code, $this->getTeamNeon()->status_colors) ? $this->getTeamNeon()->status_colors[$code] : "#0275d8";
    }

    public function getEventColors() {
        return $this->getTeamNeon()->event_colors;
    }

    public function getVersions() {
        return $this->versions;
    }

    public function getVersion($index = 0) {
        return $this->versions[$index];
    }
    
    public function getVersionCode(){
        return $this->getVersion()->version;
    }

    public function setVersion() {
        $taglog = file(__DIR__ . "/../tag.log");
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

    public function getTeamNeonDir() {
        return $this->teamNeonDir;
    }

    public function setTeamNeonDir($teamNeonDir) {
        $this->teamNeonDir = $teamNeonDir;
        return $this;
    }

    public function isHttps() {
        return strtolower($this->getTapi_config()["protocol"]) == "https";
    }

    public function getTeamNeon() {
        return $this->teamNeon;
    }

    public function getUserNeon() {
        return $this->userNeon;
    }

    public function setTeamNeon($teamNeon) {
        $this->teamNeon = $teamNeon;
        return $this;
    }

    public function setUserNeon($userNeon) {
        $this->userNeon = $userNeon;
        return $this;
    }

    public function getSkin() {
        if ($this->getUserNeon() != NULL && !empty($this->getUserNeon()->skin))
            return $this->getUserNeon()->skin;
        else
            return $this->getTeamNeon()->skin;
    }

    public function getRequiredFields() {
        return $this->getTeamNeon()->userRequiredFields;
    }

    public function getAllSkins() {
        return $this->allSkins;
    }

    public function setAllSkins($allSkins) {
        $this->allSkins = $allSkins;
        return $this;
    }

    private function getUserNeonFile($userId){
        return $this->getTeamNeonDir() . "/config.user.$userId.neon";
    }
    
    private function getTeamNeonFile(){
        return $this->getTeamNeonDir() . "/config.team.neon";
    }
}
