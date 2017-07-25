<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Presenters;

use Nette;
use Nette\Application\UI\NavbarControl;

/**
 * Description of SecuredPresenter
 *
 * @author matej
 */
class SecuredPresenter extends BasePresenter {
    
    protected $levelCaptions;
    
    /** @var \App\Model\TapiAuthenticator @inject */
    public $tapiAuthenticator;
    
    /** @var \App\Model\TapiAuthorizator @inject */
    public $tapiAuthorizator;
    
    /** @var \Tymy\Discussions @inject */
    public $discussions;
    
    /** @var \Tymy\Polls @inject */
    public $polls;
    
    /** @var \Tymy\Events @inject */
    public $events;
    
    /** @var \Tymy\User @inject */
    public $user;
    
    /** @var \Tymy\Users @inject */
    public $users;
    
    /** @var \Tymy\EventTypes @inject */
    public $eventTypes;
    
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
    
    protected function getEventTypes($force = FALSE){
        $sessionSection = $this->getSession()->getSection("tymy");
        
        if(isset($sessionSection["eventTypes"]) && !$force)
            return $sessionSection["eventTypes"];
        
        $eventTypesResult = $this->eventTypes->fetch();
        
        $eventTypes = [];
        foreach ($eventTypesResult as $type) {
            $eventTypes[$type->code] = $type;
            $preStatusSet = [];
            foreach ($type->preStatusSet as $preSS) {
                $preStatusSet[$preSS->code] = $preSS;
            }
            $eventTypes[$type->code]->preStatusSet = $preStatusSet;
            
            $postStatusSet = [];
            foreach ($type->postStatusSet as $postSS) {
                $postStatusSet[$postSS->code] = $postSS;
            }
            $eventTypes[$type->code]->postStatusSet = $postStatusSet;
        }
        $sessionSection["eventTypes"] = $eventTypes;
        return $eventTypes;
    }
    
    public function getUsers($force = FALSE){
        $sessionSection = $this->getSession()->getSection("tymy");
        if(isset($sessionSection["users"]) && !$force)
            return $sessionSection["users"];
        $this->users->fetch();
        \Tracy\Debugger::barDump($this->users->getResult());
        $sessionSection["users"] = $this->users->getResult();
        $sessionSection["me"] = $this->users->getResult()->me;
        return $this->users->getResult();
    }

    protected function startup() {
        parent::startup();
        if (!$this->getUser()->isLoggedIn()) {
            if ($this->getUser()->getLogoutReason() === Nette\Security\IUserStorage::INACTIVITY) {
                $this->flashMessage('You have been signed out due to inactivity. Please sign in again.');
            }
            $this->redirect('Sign:in', ['backlink' => $this->storeRequest()]);
        }
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
    
}
