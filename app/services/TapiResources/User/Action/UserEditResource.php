<?php

namespace Tapi;
use Tapi\Exception\APIException;

/**
 * Project: tymy_v2
 * Description of UserEditResource
 *
 * @author kminekmatej created on 29.12.2017, 19:58:33
 */
class UserEditResource extends UserResource {
    
    public function init() {
        parent::globalInit();
        $this->setCacheable(FALSE);
        $this->setMethod(RequestMethod::PUT);
        $this->setUser(NULL);
        return $this;
    }

    protected function preProcess() {
        if($this->getId() == null)
            throw new APIException('User ID is missing', self::BAD_REQUEST);
        
        if (!$this->getUser())
            throw new APIException('User object is missing', self::BAD_REQUEST);
        
        $this->setUrl("users/" . $this->getId());
        $this->setRequestData($this->getUser());

        return $this;
    }
    
    protected function postProcess() {
        $this->clearCache($this->getId());
    }
    
    public function getUser() {
        return $this->options->user;
    }

    public function setUser($user) {
        $this->options->user = $user;
        return $this;
    }


}
