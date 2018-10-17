<?php

namespace Tapi;
use Tapi\Exception\APIException;

/**
 * Project: tymy_v2
 * Description of PollCreateResource
 *
 * @author kminekmatej created on 5.1.2018, 9:59:23
 */
class PollCreateResource extends PollResource {
    
    public function init() {
        parent::globalInit();
        $this->setCacheable(FALSE);
        $this->setMethod(RequestMethod::POST);
        $this->setPoll(NULL);
        return $this;
    }

    protected function preProcess() {
        if($this->getPoll() == null)
            throw new APIException('Poll object is missing', self::BAD_REQUEST);
        
        $this->setUrl("polls");
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
