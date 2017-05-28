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

    protected function startup() {
        parent::startup();
        \Tracy\Debugger::barDump($this->tapiAuthenticator, "Authenticator load");
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
