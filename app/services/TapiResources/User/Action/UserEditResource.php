<?php

namespace Tapi;
use Tymy\Exception\APIException;

/**
 * Project: tymy_v2
 * Description of UserEditResource
 *
 * @author kminekmatej created on 29.12.2017, 19:58:33
 */
class UserEditResource extends UserResource {
    
    private $userData;
    
    public function init() {
        $this->setCacheable(FALSE);
        $this->setMethod(RequestMethod::PUT);
    }

    protected function preProcess() {
        if($this->getId() == null)
            throw new APIException('ID not set!');
        
        if (!$this->getUserData())
            throw new APIException('User not set!');
        
        $this->setUrl("users/" . $this->getId());
        $this->setRequestData($this->userData);

        return $this;
    }
    
    protected function postProcess() {
        $this->clearCache($this->getId());
    }
    
    public function getUserData() {
        return $this->userData;
    }

    public function setUserData($userData) {
        $this->userData = $userData;
        return $this;
    }


}
