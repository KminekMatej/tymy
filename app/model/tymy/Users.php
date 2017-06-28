<?php

namespace Tymy;

use Nette;
use Nette\Utils\Strings;

/**
 * Description of Tymy
 *
 * @author matej
 */
final class Users extends UserInterface{
    
    private $userType;
    
    public function __construct(\App\Model\TapiAuthenticator $tapiAuthenticator = NULL, Nette\Application\UI\Presenter $presenter = NULL, $userType = NULL) {
        parent::__construct($tapiAuthenticator, $presenter);
        $this->userType = $userType;
    }
    
    public function select() {
        $this->fullUrl .= "users/";
        if(!is_null($this->userType))
            $this->fullUrl .= "status/" . $this->userType . "/";
        return $this;
    }

    protected function postProcess(){
        $data = $this->getData();
        
        $myId = isset($this->presenter) ? $this->presenter->user->getId() : NULL;
        
        $this->getResult()->menuWarningCount = 0;
        
        $counts = [
            "ALL"=>0,
            "NEW"=>0,
            "PLAYER"=>0,
            "MEMBER"=>0,
            "SICK"=>0,
            "DELETED"=>0,
            "INIT"=>0,
            ];
        
        $players = [];
        foreach ($data as $player) {
            $counts["ALL"]++;
            $counts[$player->status]++;
            if(!property_exists($player, "gender")) $player->gender = "UNKNOWN"; //set default value
            
            $player->webName = Strings::webalize($player->fullName);
            $this->userWarnings($player);
            $this->userPermissions($player);
            $players[$player->id] = $player;
            if($player->id == $myId){
                $this->getResult()->menuWarningCount = $player->errCnt;
                $this->getResult()->me = (object)$player;
            }
            $player->webName = Strings::webalize($player->fullName);
            if(property_exists($player, "lastLogin")){
                $this->timezone($player->lastLogin);
            }
            $players[$player->id] = $player;
        }
        $this->getResult()->data = $players;
        $this->getResult()->counts = $counts;
    }
    
    
}
