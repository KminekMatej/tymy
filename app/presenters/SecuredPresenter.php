<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Presenters;

use Nette;
use Nette\Caching\Cache;
use Nette\Caching\Storages\FileStorage;
use Nette\Application\UI\NavbarControl;
use App\Model\SettingMenu;
use App\Model\TapiAuthenticator;
use App\Model\TapiAuthorizator;
use Tracy\Debugger;
use Tapi\TapiObject;
use Tapi\EventListResource;
use Tapi\EventTypeListResource;
use Tapi\DiscussionListResource;
use Tapi\DiscussionNewsListResource;
use Tapi\UserDetailResource;
use Tapi\UserListResource;
use Tapi\PollListResource;
use Tapi\NoteListResource;
use Tapi\Exception\APIException;
use Tapi\AuthDetailResource;

/**
 * Description of SecuredPresenter
 *
 * @author matej
 */
class SecuredPresenter extends BasePresenter {
    
    protected $levelCaptions;
    
    /** @var TapiAuthenticator @inject */
    public $tapiAuthenticator;
    
    /** @var TapiAuthorizator @inject */
    public $tapiAuthorizator;
    
    /** @var DiscussionListResource @inject */
    public $discussionList;
    
    /** @var DiscussionNewsListResource @inject */
    public $discussionNews;
    
    /** @var PollListResource @inject */
    public $polls;
    
    /** @var EventListResource @inject */
    public $eventList;
    
    /** @var UserDetailResource @inject */
    public $userDetail;
        
    /** @var UserListResource @inject */
    public $userList;
    
    /** @var AuthDetailResource @inject */
    public $apiRights;
    
    /** @var EventTypeListResource @inject */
    public $eventTypeList;
    
    /** @var NoteListResource @inject */
    public $noteList;
    
    /** @var FileStorage @inject */
    public $cacheStorage;
    
    public $accessibleSettings = [];
    
    public function getLevelCaptions(){
        return $this->levelCaptions;
    }
    
    public function setLevelCaptions($levelCaptions){
        if(!is_array($levelCaptions)) return false;
        foreach ($levelCaptions as $level => $caption) {
            $this->levelCaptions[$level] = $caption;
        }
        
        for ($index = max(array_keys($levelCaptions))+1; $index < count($this->levelCaptions); $index++) {
            unset($this->levelCaptions[$index]);
        }
    }
        
    protected function startup() {
        parent::startup();
        Debugger::$maxDepth = 7;
        if (!$this->getUser()->isLoggedIn()) {
            if ($this->getUser()->getLogoutReason() === Nette\Security\IUserStorage::INACTIVITY) {
                $this->flashMessage('You have been signed out due to inactivity. Please sign in again.');
            }
            $this->redirect('Sign:in');
        }
        //$this->cacheService->dropCache();
        
        $this->supplier->setTapi_config($this->getUser()->getIdentity()->getData()["tapi_config"]);
        $this->apiRights->setId($this->getUser()->getId());
        $this->apiRights->getData();
        $this->tapiAuthorizator->setUser($this->getUser()->getIdentity()->getData());
        $this->tapiAuthorizator->setApiRights($this->apiRights);
        
        $this->setAccessibleSettings();
        $this->setLevelCaptions(["0" => ["caption" => "Hlavní stránka", "link" => $this->link("Homepage:")]]);
        $this->template->tym = $this->supplier->getTym();
        $this->template->noteList = $this->noteList->init()->getData();
        $this->showNotes();
    }
    
    protected function shutdown($response) {
        parent::shutdown($response);
        $cache = new Cache($this->cacheStorage, TapiObject::CACHE_STORAGE);
        $allKeys = $cache->load("allkeys");
        $cache_dump = [];
        if ($allKeys) {
            foreach ($allKeys as $key) {
                $val = $cache->load($key);
                if (!is_null($val))
                    $cache_dump[$key] = $val;
            }
        }
        Debugger::barDump($cache_dump, "Cache contents");
    }
    
    protected function createComponentNavbar() {
        $navbar = new NavbarControl($this);
        $navbar->redrawControl();
        return $navbar;
    }
    
    protected function parseIdFromWebname($webName){
        if(strpos($webName, "-")){
            return substr($webName,0,strpos($webName, "-"));
        }
        if(intval($webName))
            return intval($webName);
    }
    
    public function handleTapiException(APIException $ex, $redirect = null, $args = []){
        $this->flashMessage("Došlo k nečekané chybě: " . $ex->getMessage(), "danger");
        if($redirect)
            $this->redirect ($redirect);
        else
            $this->error("Došlo k nečekané chybě: " . $ex->getMessage(), $ex->getCode());
    }
    
    /**
     * Smart pagination script
     * @link https://stackoverflow.com/questions/163809/smart-pagination-algorithm
     * @param int $data Total count of items
     * @param int $limit Number of items per page
     * @param int $current Number of current page
     * @param int $adjacents Number of shown links
     * @return type
     */
    protected function pagination($data, $limit = null, $current = null, $adjacents = null) {
        $result = array();

        if (isset($data, $limit) === true) {
            $result = range(1, ceil($data / $limit));

            if (isset($current, $adjacents) === true) {
                if (($adjacents = floor($adjacents / 2) * 2 + 1) >= 1) {
                    $result = array_slice($result, max(0, min(count($result) - $adjacents, intval($current) - ceil($adjacents / 2))), $adjacents);
                }
            }
        }

        return $result;
    }
    
    public function getAccessibleSettings() {
        return $this->accessibleSettings;
    }

    private function setAccessibleSettings() {
        if($this->getUser()->isAllowed('discussion','setup')) $this->accessibleSettings[] = new SettingMenu("discussions", "Diskuze", $this->link("Settings:discussions"), "fa-comments", TRUE);
        if($this->getUser()->isAllowed('event','canUpdate')) $this->accessibleSettings[] = new SettingMenu("events", "Události", $this->link("Settings:events"), "fa-calendar", TRUE);
        //TO BE ENABLED WHEN ITS READY
        if($this->getUser()->isAllowed('team','canSetup')) $this->accessibleSettings[] = new SettingMenu("team", "Tým", $this->link("Settings:team"), "fa-users", FALSE);
        // TO BE ENABLED WHEN ITS READY
        if($this->getUser()->isAllowed('poll','canUpdatePoll')) $this->accessibleSettings[] = new SettingMenu("polls", "Ankety", $this->link("Settings:polls"), "fa-chart-pie", TRUE);
        $this->accessibleSettings[] = new SettingMenu("notes", "Poznámky", $this->link("Settings:notes"), "fa-sticky-note", TRUE); //user can always manage at least his own notes
        // TO BE ENABLED WHEN ITS READY
        if($this->getUser()->isAllowed('reports','canSetup')) $this->accessibleSettings[] = new SettingMenu("reports", "Reporty", $this->link("Settings:reports"), "fa-chart-area", FALSE);
        // TO BE ENABLED WHEN ITS READY
        if($this->getUser()->isAllowed('permissions','canSetup')) $this->accessibleSettings[] = new SettingMenu("permissions", "Oprávnění", $this->link("Settings:permissions"), "fa-gavel", FALSE);
        $this->accessibleSettings[] = new SettingMenu("app", "Aplikace", $this->link("Settings:app"), "fa-laptop", TRUE); //user can always look into app settings to setup his own properties
        return $this;
    }
    
    protected function showNotes($recordId = NULL) {
        $notesToShow = [];
        $presenterName = [
            'WELCOME' => "Homepage",
            'DISKUZE' => "Discussion",
            'UDALOST' => "Event",
            'ANKETA' => "Poll",
            'TYM' => "Team",
            'NASTAVENI' => "Settings",
            'POZNAMKY' => "Notes",
        ];
        if($this->template->noteList == null)
            return;
        foreach ($this->template->noteList as $note) {
            $display = explode(":", $note->specialPage);
            $displayPresenter = $display[0];
            $displayRule = NULL;
            $displayId = NULL;
            if(count($display) == 2){
                if(is_numeric($display[1])){
                    $displayId = $display[1];
                } else {
                    $displayRule = $display[1];
                }
            } else if (count($display) > 2){
                $displayId = $display[1];
                $displayRule = $display[2];
            }
            $displayPresenterPassed = array_key_exists($displayPresenter, $presenterName) && $presenterName[$displayPresenter] == $this->getRequest()->presenterName;
            switch ($displayRule) {
                case NULL:
                    $displayRulePassed = !array_key_exists('lastLogin', $this->getUser()->getIdentity()->getData()) && !$note->shown; //when rule is not filled, display only on first login
                    break;
                case "ALWAYS":
                    $displayRulePassed = TRUE;
                    break;
                case "NEW":
                    $displayRulePassed = $this->getUser()->getIdentity()->getData()["isNew"];
                    break;
                default:
                    $displayRulePassed = FALSE;
                    break;
            }
            $displayIdPassed = is_null($displayId) ? TRUE : ($this->getUser()->getId() == $displayId && $displayPresenter=="WELCOME") || $recordId == $displayId;
            if($displayPresenterPassed && $displayRulePassed && $displayIdPassed){
                $notesToShow[] = str_replace(":", "_", $note->specialPage);
                $note->shown = TRUE;
            }
        }
        $this->template->notesToShow = $notesToShow;
        $this->noteList->saveToCache();
    }
    
}
