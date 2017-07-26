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
    
    const TAPI_NAME = "users";
    const TSID_REQUIRED = TRUE;
    private $userType;
    
    public function getUserType() {
        return $this->userType;
    }

    public function setUserType($userType) {
        $this->userType = $userType;
        return $this;
    }
    
    public function select() {
        $this->fullUrl .= self::TAPI_NAME;
        if(!is_null($this->userType))
            $this->fullUrl .= "/status/" . $this->userType;
        return $this;
    }

    protected function postProcess(){
        $data = $this->getData();
        
        $myId = $this->user->getId();
        
        $this->result->menuWarningCount = 0;
        
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
            
            $player->webName = Strings::webalize($player->id . "-" . $player->fullName);
            $this->userWarnings($player);
            $this->userPermissions($player);
            $players[$player->id] = $player;
            if($player->id == $myId){
                $this->result->menuWarningCount = $player->errCnt;
                $this->result->me = (object)$player;
            }
            if(property_exists($player, "lastLogin"))   $this->timezone($player->lastLogin);
            $this->timezone($player->createdAt);
            if($player->isNew = strtotime($player->createdAt) > strtotime("- 14 days")){
                $counts["NEW"]++;
            }
            $players[$player->id] = $player;
        }
        $this->result->data = $players;
        $this->result->counts = $counts;
        
        $this->session->getSection(self::SESSION_SECTION)[$this->getTapiName()] = $this->result;
        
    }
    
    public function reset() {
        $this->userType = NULL;
        return parent::reset();
    }

}
