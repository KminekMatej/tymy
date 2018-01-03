<?php

namespace Tapi;

/**
 * Project: tymy_v2
 * Description of UserListResource
 *
 * @author kminekmatej created on 29.12.2017, 19:57:30
 */
class UserListResource extends UserResource {
    
    public function init() {
        $this->setCachingTimeout(CacheService::TIMEOUT_LARGE);
        $this->setUserType(NULL);
        $this->options->byId = null;
        $this->options->me = null;
        $this->options->counts = null;
        
    }

    protected function preProcess() {
        $this->setUrl(is_null($this->getUserType()) ? "users" : "users/status/" . $this->getUserType());
        return $this;
    }
    
    protected function postProcess() {
        $this->options->warnings = 0;
        $this->options->byId = [];
        $myId = $this->user->getId();
        
        $this->options->counts = [
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
            $this->options->counts["ALL"]++;
            $this->options->counts[$user->status]++;
            if($user->id == $myId){
                $this->options->warnings = $user->errCnt;
                $this->options->me = (object)$user;
            }
            if($user->isNew = strtotime($user->createdAt) > strtotime("- 14 days")){
                $this->options->counts["NEW"]++;
                if($user->status == "PLAYER")
                    $this->options->counts["NEW:PLAYER"]++;
            }
            $this->options->byId[$user->id] = $user;
        }
    }
    
    public function getUserType() {
        return $this->options->userType;
    }

    public function setUserType($userType) {
        $this->options->userType = $userType;
        return $this;
    }
    
    public function getCounts() {
        return $this->options->counts;
    }

    public function getById() {
        return $this->options->byId;
    }

    public function getMe() {
        return $this->options->me;
    }

    
    protected function getClassCacheName() {
        $ccName = parent::getClassCacheName();
        if (!is_null($this->options->userType)) {
            $ccName .= ":" . $this->options->userType;
        }
        return $ccName;
    }

}
