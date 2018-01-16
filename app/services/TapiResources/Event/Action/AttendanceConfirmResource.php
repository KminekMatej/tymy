<?php

namespace Tapi;

/**
 * Project: tymy_v2
 * Description of AttendanceConfirmResource
 *
 * @author kminekmatej created on 2.1.2018, 21:31:39
 */
class AttendanceConfirmResource extends EventResource {

    public function init() {
        $this->setCacheable(FALSE);
        $this->setMethod(RequestMethod::POST);
        $this->setPostStatuses(NULL);
    }

    protected function preProcess() {
        $this->setUrl("attendance");
        
        foreach ($this->options->postStatuses as &$status) {
            $status["eventId"] = $this->getId();
        }
        
        $this->setRequestData($this->getPostStatuses());
    }

    protected function postProcess() {
        $this->clearCache($this->getId());
    }
    
    public function getPostStatuses() {
        return $this->options->postStatuses;
    }

    public function setPostStatuses($postStatuses) {
        $this->options->postStatuses = $postStatuses;
        return $this;
    }
    
}
