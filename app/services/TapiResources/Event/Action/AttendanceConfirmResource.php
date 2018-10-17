<?php

namespace Tapi;
use Tapi\Exception\APIException;

/**
 * Project: tymy_v2
 * Description of AttendanceConfirmResource
 *
 * @author kminekmatej created on 2.1.2018, 21:31:39
 */
class AttendanceConfirmResource extends EventResource {

    public function init() {
        parent::globalInit();
        $this->setCacheable(FALSE);
        $this->setMethod(RequestMethod::POST);
        $this->setPostStatuses(NULL);
        return $this;
    }

    protected function preProcess() {
        if($this->getId() == null)
            throw new APIException('Event ID is missing', self::BAD_REQUEST);
        if($this->getPostStatuses() == null)
            throw new APIException('Statuses object is missing', self::BAD_REQUEST);
        
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
