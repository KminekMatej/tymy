<?php

namespace Tapi;
use Tapi\Exception\APIException;

/**
 * Project: tymy_v2
 * Description of EventAttendanceResource
 *
 * @author kminekmatej created on 2.1.2018, 21:30:10
 */
class AttendancePlanResource extends EventResource {
    
    public function init() {
        parent::globalInit();
        $this->setCacheable(FALSE);
        $this->setMethod(RequestMethod::POST);
        $this->setPreStatus(NULL);
        $this->setPreDescription(NULL);
        return $this;
    }

    protected function preProcess() {
        if($this->getId() == null)
            throw new APIException('Event ID is missing', self::BAD_REQUEST);
        if($this->getPreStatus() == null)
            throw new APIException('Status is missing', self::BAD_REQUEST);
        
        $this->setUrl("attendance");
        
        $data = [
            "userId" => $this->user->getId(),
            "eventId" => $this->getId(),
            "preStatus" => $this->getPreStatus(),
            "preDescription" => $this->getPreDescription()
        ];
        
        $this->setRequestData([(object)$data]);
    }

    protected function postProcess() {
        $this->clearCache($this->getId());
    }
    
    public function getPreStatus() {
        return $this->options->preStatus;
    }

    public function getPreDescription() {
        return $this->options->preDescription;
    }

    public function setPreStatus($preStatus) {
        $this->options->preStatus = $preStatus;
        return $this;
    }

    public function setPreDescription($preDescription) {
        $this->options->preDescription = $preDescription;
        return $this;
    }


    
}
