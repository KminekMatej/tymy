<?php

namespace App\Presenters;

use Nette;
use App\Model;
use Nette\Application\UI\Form;
use Nette\Utils\Strings;

class TeamPresenter extends SecuredPresenter {
    
    private $userType;
    
    public function __construct() {
        parent::__construct();
    }
    
    public function startup() {
        parent::startup();
        $this->setLevelCaptions(["0" => ["caption" => "Tým", "link" => $this->link("Team:") ] ]);
    }
    
    public function actionPlayers() {
        $this->setLevelCaptions(["1" => ["caption" => "Hráči", "link" => $this->link("Team:players") ] ]);
        $this->userType = "PLAYER";
        $this->setView('default');
    }
    
    public function actionMembers() {
        $this->setLevelCaptions(["1" => ["caption" => "Členové", "link" => $this->link("Team:members") ] ]);
        $this->userType = "MEMBER";
        $this->setView('default');
    }
    
    public function actionSicks() {
        $this->setLevelCaptions(["1" => ["caption" => "Marodi", "link" => $this->link("Team:sicks") ] ]);
        $this->userType = "SICK";
        $this->setView('default');
    }
    
    public function renderDefault() {
        $users = new \Tymy\Users($this->tapiAuthenticator, $this, $this->userType);
        $this->template->users = $users->fetch();;
    }
    
    public function renderPlayer($player) {
        $players = new \Tymy\Users($this->tapiAuthenticator, $this);
        $playerId = NULL;
        foreach ($players->fetch() as $p) {
            if($p->webName == $player){
                $playerId = $p->id;
                $this->setLevelCaptions(["1" => ["caption" => $p->callName, "link" => $this->link("Team:player", $p->webName) ] ]);
                break;
            }
        }
        
        $playerObj = new \Tymy\User($this->tapiAuthenticator, $this);
        $playerData = $playerObj->
                recId($playerId)->
                fetch();
        
        //set default values to avoid latte exceptions
        if(!isset($playerData->firstName)) $playerData->firstName = "";
        if(!isset($playerData->lastName)) $playerData->lastName = "";
        if(!isset($playerData->login)) $playerData->login = "";
        if(!isset($playerData->callName)) $playerData->callName = "";
        if(!isset($playerData->jerseyNumber)) $playerData->jerseyNumber = "";
        if(!isset($playerData->street)) $playerData->street = "";
        if(!isset($playerData->city)) $playerData->city = "";
        if(!isset($playerData->zipCode)) $playerData->zipCode = "";
        if(!isset($playerData->birthDate)) $playerData->birthDate = "";
        if(!isset($playerData->phone)) $playerData->phone = "";
        if(!isset($playerData->phone2)) $playerData->phone2 = "";
        if(!isset($playerData->email)) $playerData->email = "";
        
        $this->template->player = $playerData;
    }
    
}
