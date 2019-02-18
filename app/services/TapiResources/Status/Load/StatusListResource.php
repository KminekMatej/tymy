<?php

namespace Tapi;

/**
 * Description of StatusListResource
 *
 * @author kminekmatej, 18.2.2019
 */
class StatusListResource extends StatusResource{
    
    public function init() {
        parent::globalInit();
        $this->setCachingTimeout(TapiObject::CACHE_TIMEOUT_LARGE);
        $this->options->allCodes = [];
        return $this;
    }
    
    protected function preProcess() {
        $this->setUrl("attendanceStatus");
        return $this;
    }
    
    protected function postProcess() {
        if ($this->data == null)
            return null;
        foreach ($this->data as $statusSet) {
            parent::postProcessStatusSet($statusSet);
            $this->options->allCodes = array_merge($this->options->allCodes, $statusSet->statusesByCode);
        }
    }
    
    public function getStatusesByCode(){
        return $this->options->allCodes;
    }

}
