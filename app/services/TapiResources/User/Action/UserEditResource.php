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
        
        if ($this->getUser())
            throw new APIException('User not set!');
        
        $this->setUrl("users/" . $this->getId());
        $this->setRequestData($this->userDaa);

        return $this;
    }
    
    protected function postProcess() {
        $this->postProcessUser($this->data);
    }
    
    public function getUserData() {
        return $this->userDaa;
    }

    public function setUserData($userData) {
        $this->userDaa = $userData;
        return $this;
    }


}
