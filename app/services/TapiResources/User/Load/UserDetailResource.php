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
    
    protected function init() {
        $this->setCachingTimeout(CacheService::TIMEOUT_LARGE);
    }

    protected function preProcess() {
        if($this->getId() == null)
            throw new APIException('ID not set!');
        
        $this->setUrl("user/" . $this->getId());

        return $this;
    }
    
    protected function postProcess() {
        parent::postProcessUser($this->data);
    }
    
}
