<?php

namespace Tapi;
use Tapi\Exception\APIException;

/**
 * Project: tymy_v2
 * Description of EventCreateResource
 *
 * @author kminekmatej created on 22.12.2017, 21:09:04
 */
class EventCreateResource extends EventResource {
    
    public function init() {
        parent::globalInit();
        $this->setCacheable(FALSE);
        $this->setMethod(RequestMethod::POST);
        $this->setEventsArray(NULL);
        $this->setEventTypesArray(NULL);
        return $this;
    }
    
    protected function preProcess() {
        if($this->getEventsArray() == null)
            throw new APIException('Event array not set!');
        if($this->getEventTypesArray() == null)
            throw new APIException('Event types array not set!');
        foreach ($this->options->eventsArray as $event) {
            if(!array_key_exists("startTime", $event))
                throw new APIException('Start time not set!');
            if(!array_key_exists("type", $event))
                throw new APIException('Type not set!');
            if(!array_key_exists($event["type"], $this->getEventTypesArray()))
                throw new APIException('Unrecognized type!');
            foreach ($event as $key => $value) {
                if (in_array($key, ["startTime", "endTime", "closeTime"]))
                    $this->timeSave($value);
            }
        }
        
        $this->setUrl("events");
        $this->setRequestData($this->getEventsArray());

        return $this;
    }

    protected function postProcess() {
        $this->clearCache();
        parent::postProcessEvent($this->data);
    }
    
    public function getEventsArray() {
        return $this->options->eventsArray;
    }

    public function getEventTypesArray() {
        return $this->options->eventTypesArray;
    }

    public function setEventsArray($eventsArray) {
        $this->options->eventsArray = $eventsArray;
        return $this;
    }

    public function setEventTypesArray($eventTypesArray) {
        $this->options->eventTypesArray = $eventTypesArray;
        return $this;
    }



}
