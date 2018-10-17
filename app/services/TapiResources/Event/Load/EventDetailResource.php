<?php

namespace Tapi;
use Tapi\Exception\APIException;

/**
 * Project: tymy_v2
 * Description of EventDetailResource
 *
 * @author kminekmatej created on 22.12.2017, 21:08:02
 */
class EventDetailResource extends EventResource {
    
    public function init() {
        parent::globalInit();
        //everything inited correctly
        return $this;
    }

    protected function preProcess() {
        if($this->getId() == null)
            throw new APIException('Event ID is missing', self::BAD_REQUEST);
        $this->setUrl("event/" . $this->getId());
    }
    
    protected function postProcess() {
        parent::postProcessEvent($this->data);
    }

}
