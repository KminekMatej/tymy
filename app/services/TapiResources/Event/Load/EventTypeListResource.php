<?php

namespace Tapi;

/**
 * Project: tymy_v2
 * Description of EventTypeListResource
 *
 * @author kminekmatej created on 27.12.2017, 16:46:45
 */
class EventTypeListResource extends EventResource {
    
    public function init() {
        $this->setCacheable(TapiObject::CACHE_TIMEOUT_LARGE);
    }
    
    protected function preProcess() {
        $this->setUrl("eventTypes");
    }
    
    protected function postProcess() {
        $newData = [];
        foreach ($this->data as $value) {
            $newPreStatus = [];
            foreach ($value->preStatusSet as $status) {
                $newPreStatus[$status->code] = $status;
            }
            $value->preStatusSet = $newPreStatus;
            $newPostStatus = [];
            foreach ($value->postStatusSet as $status) {
                $newPostStatus[$status->code] = $status;
            }
            $value->postStatusSet = $newPostStatus;
            $newData[$value->code] = $value;
        }
        $this->data = $newData;
    }
    
}
