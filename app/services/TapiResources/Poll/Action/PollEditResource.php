<?php

namespace Tapi;
use Tapi\Exception\APIException;

/**
 * Project: tymy_v2
 * Description of PollEditResource
 *
 * @author kminekmatej created on 5.1.2018, 9:59:49
 */
class PollEditResource extends PollResource {
    
    public function init() {
        parent::globalInit();
        $this->setCacheable(FALSE);
        $this->setMethod(RequestMethod::PUT);
        $this->setPoll(NULL);
        return $this;
    }

    protected function preProcess() {
        if($this->getId() == null)
            throw new APIException('Poll ID not set!');
        
        $this->setUrl("polls/" . $this->getId());
        $this->options->poll["id"] = $this->getId();
        $this->setRequestData($this->getPoll());
    }

    protected function postProcess() {
        $this->clearCache($this->getId());
    }
    
    public function getPoll() {
        return $this->options->poll;
    }

    public function setPoll($poll) {
        $this->options->poll = $poll;
        return $this;
    }


}
