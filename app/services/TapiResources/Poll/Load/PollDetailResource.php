<?php

namespace Tapi;
use Tymy\Exception\APIException;

/**
 * Project: tymy_v2
 * Description of PollDetailResource
 *
 * @author kminekmatej created on 5.1.2018, 9:52:03
 */
class PollDetailResource extends PollResource {
    
    public function init() {
        $this->setCachingTimeout(CacheService::TIMEOUT_LARGE);
    }

    protected function preProcess() {
        if($this->getId() == null)
            throw new APIException('Poll ID not set!');
        $this->setUrl("polls/" . $this->getId());
        return $this;
    }

    protected function postProcess() {
        $this->postProcessPoll($this->data);
    }
    
}
