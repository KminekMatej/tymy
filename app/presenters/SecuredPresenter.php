<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Presenters;

use Nette;
use Nette\Application\UI\NavbarControl;
use App\Model\SettingMenu;
use App\Model\TapiAuthenticator;
use App\Model\TapiAuthorizator;
use Tapi\EventListResource;
use Tapi\EventTypeListResource;
use Tapi\DiscussionListResource;
use Tapi\DiscussionNewsListResource;

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
    
    /** @var \Tymy\Polls @inject */
    public $polls;
    
    /** @var EventListResource @inject */
    public $eventList;
    
    /** @var \Tymy\User @inject */
    public $user;
    
    /** @var \Tymy\Users @inject */
    public $users;
    
    /** @var EventTypeListResource @inject */
    public $eventTypeList;
    
    
    /** @var \Tapi\CacheService @inject */
    public $cacheService;
    
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
        if (!$this->getUser()->isLoggedIn()) {
            if ($this->getUser()->getLogoutReason() === Nette\Security\IUserStorage::INACTIVITY) {
                $this->flashMessage('You have been signed out due to inactivity. Please sign in again.');
            }
            $this->redirect('Sign:in');
        }
        //$this->cacheService->dropCache();
        $this->setAccessibleSettings();
        $this->supplier->setTapi_config($this->getUser()->getIdentity()->getData()["tapi_config"]);
        $this->tapiAuthorizator->setUser($this->getUser()->getIdentity()->getData()["data"]);
        $this->setLevelCaptions(["0" => ["caption" => "Hlavní stránka", "link" => $this->link("Homepage:")]]);
        $this->template->tym = $this->supplier->getTym();
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
    
    protected function handleTapiException(\Tymy\Exception\APIException $ex, $redirect = null, $args = []){
        $this->flashMessage("Došlo k nečekané chybě: ||" . $ex->getMessage());
        $this->redirect($redirect == NULL ? $this->getName() . ":default" : $redirect, $args);
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
        if($this->getUser()->isAllowed('settings','discussions')) $this->accessibleSettings[] = new SettingMenu("discussions", "Diskuze", $this->link("Settings:discussions"), "fa-comments", TRUE);
        if($this->getUser()->isAllowed('settings','events')) $this->accessibleSettings[] = new SettingMenu("events", "Události", $this->link("Settings:events"), "fa-calendar-o", TRUE);
        //TO BE ENABLED WHEN ITS READY
        if($this->getUser()->isAllowed('settings','team')) $this->accessibleSettings[] = new SettingMenu("team", "Tým", $this->link("Settings:team"), "fa-users", FALSE);
        // TO BE ENABLED WHEN ITS READY
        if($this->getUser()->isAllowed('settings','polls')) $this->accessibleSettings[] = new SettingMenu("polls", "Ankety", $this->link("Settings:polls"), "fa-pie-chart", TRUE);
        // TO BE ENABLED WHEN ITS READY
        if($this->getUser()->isAllowed('settings','reports')) $this->accessibleSettings[] = new SettingMenu("reports", "Reporty", $this->link("Settings:reports"), "fa-bar-chart", FALSE);
        // TO BE ENABLED WHEN ITS READY
        if($this->getUser()->isAllowed('settings','permissions')) $this->accessibleSettings[] = new SettingMenu("permissions", "Oprávnění", $this->link("Settings:permissions"), "fa-gavel", FALSE);
        if($this->getUser()->isAllowed('settings','app')) $this->accessibleSettings[] = new SettingMenu("app", "Aplikace", $this->link("Settings:app"), "fa-laptop", TRUE);
        return $this;
    }


}
