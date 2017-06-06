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
    
    /** @var \App\Model\TymyUserManager @inject */
    public $tapiAuthenticator;
    
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
        
        $usersObj = new \Tymy\Users($this->tapiAuthenticator, $this);
        $usersObj->fetch();
        $sessionSection["users"] = $usersObj->getResult();
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
        
        $sessionTymy = $this->getSession()->getSection("tymy");
        $sessionTymy->tym = $this->getUser()->getIdentity()->tym;
    }
    
    protected function createComponentNavbar() {
        $navbar = new NavbarControl($this);
        $navbar->redrawControl();
        return $navbar;
    }

}
