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
    
    public function select() {
        $this->fullUrl .= "users/";
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
            
            $player->webName = Strings::webalize($player->fullName);
            $this->userWarnings($player);
            $this->userPermissions($player);
            $players[$player->id] = $player;
            if($player->id == $myId){
                $this->result->menuWarningCount = $player->errCnt;
                $this->result->me = (object)$player;
            }
            $player->webName = Strings::webalize($player->fullName);
            if(property_exists($player, "lastLogin")){
                $this->timezone($player->lastLogin);
            }
            $players[$player->id] = $player;
        }
        $this->result->data = $players;
        $this->result->counts = $counts;
        
        $this->session->getSection(self::SESSION_SECTION)[$this->getTapiName()] = $this->result;
        
    }

}
