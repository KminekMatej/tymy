<?php

namespace Tapi;
use Tapi\Exception\APIException;

/**
 * Project: tymy_v2
 * Description of EventDeleteResource
 *
 * @author kminekmatej created on 22.12.2017, 21:09:04
 */
class EventDeleteResource extends EventResource {
    
    public function init() {
        parent::globalInit();
        $this->setCacheable(FALSE);
        $this->setMethod(RequestMethod::DELETE);
        return $this;
    }

    protected function preProcess() {
        if($this->getId() == null)
            throw new APIException('Event ID not set', self::BAD_REQUEST);
        
        $this->setUrl("event/" . $this->getId());
    }
    
    protected function postProcess() {
        $this->clearCache($this->getId());
    }
}
