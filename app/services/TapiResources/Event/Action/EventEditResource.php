<?php

namespace Tapi;
use Tymy\Exception\APIException;

/**
 * Project: tymy_v2
 * Description of EventEditResource
 *
 * @author kminekmatej created on 22.12.2017, 21:09:04
 */
class EventEditResource extends EventResource {
    
    private $event;
    
    public function init() {
        $this->setCacheable(FALSE);
        $this->setMethod(RequestMethod::PUT);
    }

    protected function preProcess() {
        if($this->event == null)
            throw new APIException('Event not set!');
        if($this->getId() == null)
            throw new APIException('Event id not set!');
        
        $this->setUrl("event/" . $this->getId());
        $this->setRequestData($this->event);
    }
    
    protected function postProcess() {
        $this->postProcessEvent($this->data);
    }
    
    public function getEvent() {
        return $this->event;
    }

    public function setEvent($event) {
        $this->event = $event;
        return $this;
    }


}
