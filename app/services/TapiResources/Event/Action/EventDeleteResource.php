<?php

namespace Tapi;
use Tymy\Exception\APIException;

/**
 * Project: tymy_v2
 * Description of EventDeleteResource
 *
 * @author kminekmatej created on 22.12.2017, 21:09:04
 */
class EventDeleteResource extends EventResource {
    
    protected function init() {
        $this->setCacheable(FALSE);
        $this->setMethod(RequestMethod::DELETE);
    }

    protected function preProcess() {
        if($this->getId() == null)
            throw new APIException('Event ID not set!');
        
        $this->setUrl("event/" . $this->getId());
    }
    
    protected function postProcess() {
        $this->clearCache($this->getId());
    }
}
