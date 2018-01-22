<?php

namespace Tapi;
use Tapi\Exception\APIException;

/**
 * Project: tymy_v2
 * Description of PollDetailResource
 *
 * @author kminekmatej created on 5.1.2018, 9:52:03
 */
class PollDetailResource extends PollResource {
    
    public function init() {
        parent::globalInit();
        $this->setCachingTimeout(TapiObject::CACHE_TIMEOUT_LARGE);
        return $this;
    }

    protected function preProcess() {
        if($this->getId() == null)
            throw new APIException('Poll ID not set');
        $this->setUrl("polls/" . $this->getId());
        return $this;
    }

    protected function postProcess() {
        $this->postProcessPoll($this->data);
    }
    
}
