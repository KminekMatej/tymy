<?php

namespace Tapi;
use Tapi\Exception\APIException;

/**
 * Project: tymy_v2
 * Description of UserCreateResource
 *
 * @author kminekmatej created on 29.12.2017, 19:58:05
 */
class UserCreateResource extends UserResource {
    
    public function init() {
        parent::globalInit();
        $this->setCacheable(FALSE);
        $this->setMethod(RequestMethod::POST);
        $this->setUser(NULL);
        return $this;
    }

    protected function preProcess() {
        if (!$this->getUser())
            throw new APIException('User object is missing', self::BAD_REQUEST);
        
        $this->setUrl("users");
        $this->setRequestData($this->getUser());

        return $this;
    }
    
    protected function postProcess() {
        parent::postProcessUser($this->data);
        $this->clearCache();
    }
    
    public function getUser() {
        return $this->options->user;
    }

    public function setUser($user) {
        $this->options->user = $user;
        return $this;
    }
    
}
