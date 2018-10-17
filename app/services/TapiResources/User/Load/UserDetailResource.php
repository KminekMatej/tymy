<?php

namespace Tapi;
use Tapi\Exception\APIException;

/**
 * Project: tymy_v2
 * Description of UserDetailResource
 *
 * @author kminekmatej created on 29.12.2017, 19:57:09
 */
class UserDetailResource extends UserResource {
    
    public function init() {
        parent::globalInit();
        $this->setCachingTimeout(TapiObject::CACHE_TIMEOUT_LARGE);
        return $this;
    }

    protected function preProcess() {
        if($this->getId() == null)
            throw new APIException('User ID is missing', self::BAD_REQUEST);
        
        $this->setUrl("user/" . $this->getId());

        return $this;
    }
    
    protected function postProcess() {
        parent::postProcessUser($this->data);
    }
    
}
