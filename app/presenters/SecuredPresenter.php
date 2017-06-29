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
        
        $eventTypesObj = new \Tymy\EventTypes($this->tapiAuthenticator, $this);
        $eventTypesResult = $eventTypesObj->fetch();
        
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
        $usersObj = new \Tymy\Users($this->tapiAuthenticator, $this, NULL);
        $usersObj->fetch();
        $sessionSection["users"] = $usersObj->getResult();
        $sessionSection["me"] = $usersObj->getResult()->me;
        return $usersObj->getResult();
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
    
}
