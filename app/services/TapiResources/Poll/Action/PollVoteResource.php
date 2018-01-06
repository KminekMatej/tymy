<?php

namespace Tapi;
use Tymy\Exception\APIException;

/**
 * Project: tymy_v2
 * Description of PollVoteResource
 *
 * @author kminekmatej created on 5.1.2018, 10:00:39
 */
class PollVoteResource extends PollResource {
    
    protected function init() {
        $this->setCacheable(FALSE);
        $this->setMethod(RequestMethod::POST);
        $this->setVotes(NULL);
    }

    protected function preProcess() {
        if($this->getId() == null)
            throw new APIException('Poll ID not set!');
        
        if($this->getVotes() == null)
            throw new APIException('Poll votes not set!');
        
        $this->setUrl("polls/" . $this->getId() . "/votes");
        
        foreach ($this->options->votes as &$vote) {
            $vote["userId"] = $this->user->getId();
        }
        $this->setPostData($this->getVotes());
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
