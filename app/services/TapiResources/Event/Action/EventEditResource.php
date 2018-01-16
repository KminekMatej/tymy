<?php

namespace Tapi;
use Tapi\Exception\APIException;

/**
 * Project: tymy_v2
 * Description of EventEditResource
 *
 * @author kminekmatej created on 22.12.2017, 21:09:04
 */
class EventEditResource extends EventResource {
    
    public function init() {
        $this->setCacheable(FALSE);
        $this->setMethod(RequestMethod::PUT);
        $this->setEvent(NULL);
    }

    protected function preProcess() {
        if($this->getEvent() == null)
            throw new APIException('Event not set!');
        if($this->getId() == null)
            throw new APIException('Event id not set!');
        
        $this->setUrl("event/" . $this->getId());
        
        foreach ($this->options->event as $key => $value) {
            if(in_array($key, ["startTime","endTime","closeTime"]))
                $this->timeSave($value);
        }
        
        $this->setRequestData($this->getEvent());
    }
    
    protected function postProcess() {
        $this->clearCache($this->getId());
    }
    
    public function getEvent() {
        return $this->options->event;
    }

    public function setEvent($event) {
        $this->options->event = $event;
        return $this;
    }


}
