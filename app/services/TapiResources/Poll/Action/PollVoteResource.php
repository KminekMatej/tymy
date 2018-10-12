<?php

namespace Tapi;
use Tapi\Exception\APIException;

/**
 * Project: tymy_v2
 * Description of PollVoteResource
 *
 * @author kminekmatej created on 5.1.2018, 10:00:39
 */
class PollVoteResource extends PollResource {
    
    public function init() {
        parent::globalInit();
        $this->setCacheable(FALSE);
        $this->setMethod(RequestMethod::POST);
        $this->setVotes(NULL);
        return $this;
    }

    protected function preProcess() {
        if($this->getId() == null)
            throw new APIException('Poll ID not set', self::BAD_REQUEST);
        
        if($this->getVotes() == null)
            throw new APIException('Poll votes object not set', self::BAD_REQUEST);
        
        $this->setUrl("polls/" . $this->getId() . "/votes");
        
        foreach ($this->options->votes as &$vote) {
            $vote["userId"] = $this->user->getId();
        }
        $this->setRequestData($this->getVotes());
    }

    protected function postProcess() {}
    
    public function getVotes() {
        return $this->options->votes;
    }

    public function setVotes($votes) {
        $this->options->votes = $votes;
        return $this;
    }



}
