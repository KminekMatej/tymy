<?php

namespace Tapi;
use Tymy\Exception\APIException;

/**
 * Project: tymy_v2
 * Description of EventCreateResource
 *
 * @author kminekmatej created on 22.12.2017, 21:09:04
 */
class EventCreateResource extends EventResource {
    
    private $eventsArray;
    private $eventTypesArray;
    
    public function init() {
        $this->setCacheable(FALSE);
        $this->setMethod(RequestMethod::POST);
    }
    
    protected function preProcess() {
        if($this->eventsArray == null)
            throw new \Tymy\Exception\APIException('Event array not set!');
        if($this->eventTypesArray == null)
            throw new \Tymy\Exception\APIException('Event types array not set!');
        foreach ($this->eventsArray as $event) {
            if(!array_key_exists("startTime", $event))
                throw new \Tymy\Exception\APIException('Start time not set!');
            if(!array_key_exists("type", $event))
                throw new \Tymy\Exception\APIException('Type not set!');
            if(!array_key_exists($event["type"], $this->eventTypesArray))
                throw new \Tymy\Exception\APIException('Unrecognized type!');
        }
        
        $this->setUrl("events");
        $this->setRequestData($this->eventsArray);

        return $this;
    }

    protected function postProcess() {
        $this->clearCache();
        $this->postProcessEvent($this->data);
    }
    
    public function getEventsArray() {
        return $this->eventsArray;
    }

    public function getEventTypesArray() {
        return $this->eventTypesArray;
    }

    public function setEventsArray($eventsArray) {
        $this->eventsArray = $eventsArray;
        return $this;
    }

    public function setEventTypesArray($eventTypesArray) {
        $this->eventTypesArray = $eventTypesArray;
        return $this;
    }



}
