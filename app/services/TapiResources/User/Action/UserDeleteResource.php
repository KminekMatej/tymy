<?php

namespace Tapi;
use Tapi\Exception\APIException;

/**
 * Project: tymy_v2
 * Description of UserDeleteResource
 *
 * @author kminekmatej created on 29.12.2017, 19:58:49
 */
class UserDeleteResource extends UserResource {
    
    public function init() {
        parent::globalInit();
        $this->setCacheable(FALSE);
        $this->setMethod(RequestMethod::PUT);
        return $this;
    }

    protected function preProcess() {
        if($this->getId() == null)
            throw new APIException('User ID not set');
        
        $this->setUrl("users/" . $this->getId());
        $this->setRequestData((object)["status" => "DELETED"]);

        return $this;
    }
    
    protected function postProcess() {
        
    }
    
}
