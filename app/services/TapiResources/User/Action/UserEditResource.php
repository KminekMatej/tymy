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
    
    protected function init() {
        $this->setCacheable(FALSE);
        $this->setMethod(RequestMethod::PUT);
    }

    protected function preProcess() {
        if($this->getId() == null)
            throw new APIException('ID not set!');
        
        if (!$this->getUserData())
            throw new APIException('User not set!');
        
        $this->setUrl("users/" . $this->getId());
        $this->setRequestData($this->getUserData());

        return $this;
    }
    
    protected function postProcess() {
        $this->clearCache($this->getId());
    }
    
    public function getUserData() {
        return $this->options->userData;
    }

    public function setUserData($userData) {
        $this->options->userData = $userData;
        return $this;
    }


}
