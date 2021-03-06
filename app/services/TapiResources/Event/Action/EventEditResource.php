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
        parent::globalInit();
        $this->setCacheable(FALSE);
        $this->setMethod(RequestMethod::PUT);
        $this->setEvent(NULL);
        return $this;
    }

    protected function preProcess() {
        if($this->getId() == null)
            throw new APIException('Event ID is missing', self::BAD_REQUEST);
        if($this->getEvent() == null)
            throw new APIException('Event object is missing', self::BAD_REQUEST);
        
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
