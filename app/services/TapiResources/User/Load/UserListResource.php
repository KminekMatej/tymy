<?php

namespace Tapi;

/**
 * Project: tymy_v2
 * Description of UserListResource
 *
 * @author kminekmatej created on 29.12.2017, 19:57:30
 */
class UserListResource extends UserResource {
    
    private $userType;
    private $byId;
    private $me;
    private $counts;
    
    public function init() {
        $this->setCachingTimeout(CacheService::TIMEOUT_LARGE);
    }

    protected function preProcess() {
        $this->setUrl(is_null($this->userType) ? "users" : "users/status/" . $this->userType);
        return $this;
    }
    
    protected function postProcess() {
        $this->warnings = 0;
        $this->byId = [];
        $myId = $this->user->getId();
        
        $this->counts = [
            "ALL"=>0,
            "NEW"=>0,
            "PLAYER"=>0,
            "NEW:PLAYER"=>0,
            "MEMBER"=>0,
            "SICK"=>0,
            "DELETED"=>0,
            "INIT"=>0,
            ];
        
        foreach ($this->data as $user) {
            parent::postProcessUser($user);
            $this->counts["ALL"]++;
            $this->counts[$user->status]++;
            if($user->id == $myId){
                $this->warnings = $user->errCnt;
                $this->me = (object)$user;
            }
            if($user->isNew = strtotime($user->createdAt) > strtotime("- 14 days")){
                $this->counts["NEW"]++;
                if($user->status == "PLAYER")
                    $this->counts["NEW:PLAYER"]++;
            }
            $this->byId[$user->id] = $user;
        }
    }
    
    public function getUserType() {
        return $this->userType;
    }

    public function setUserType($userType) {
        $this->userType = $userType;
        return $this;
    }
    
}
