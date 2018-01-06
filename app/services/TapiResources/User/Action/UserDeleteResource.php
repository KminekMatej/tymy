<?php

namespace Tapi;
use Tymy\Exception\APIException;

/**
 * Project: tymy_v2
 * Description of UserDeleteResource
 *
 * @author kminekmatej created on 29.12.2017, 19:58:49
 */
class UserDeleteResource extends UserResource {
    
    protected function init() {
        $this->setCacheable(FALSE);
        $this->setMethod(RequestMethod::PUT);
    }

    protected function preProcess() {
        if($this->getId() == null)
            throw new APIException('ID not set!');
        
        $this->setUrl("users/" . $this->getId());
        $this->setRequestData((object)["status" => "DELETED"]);

        return $this;
    }
    
    protected function postProcess() {
        
    }
    
}
