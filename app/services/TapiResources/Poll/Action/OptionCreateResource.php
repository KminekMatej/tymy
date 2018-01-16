<?php

namespace Tapi;

/**
 * Project: tymy_v2
 * Description of OptionCreateResource
 *
 * @author kminekmatej created on 5.1.2018, 15:23:48
 */
class OptionCreateResource extends PollResource {
       
    public function init() {
        $this->setCacheable(FALSE);
        $this->setMethod(RequestMethod::POST);
        $this->setPollOptions(NULL);
    }

    protected function preProcess() {
        if (!$this->getId())
            throw new APIException('Poll ID not set!');
        if (!$this->getPollOptions())
            throw new APIException('Option to create not set!');
        if (!$this->user->isAllowed("SYS", "ASK.VOTE_UPDATE"))
            throw new APIException('Permission denied!');
        foreach ($this->getPollOptions() as $option) {
            if(!array_key_exists("caption", $option))
                throw new APIException('Caption not set!');
            if(!array_key_exists("type", $option))
                throw new APIException('Type not set!');
        }
        
        $this->setUrl("polls/" . $this->getId() . "/options");
        $this->setRequestData($this->getPollOptions());

        return $this;
    }
    
    protected function postProcess() {}
    
    public function getPollOptions() {
        return $this->options->pollOptions;
    }

    public function setPollOptions($pollOptions) {
        $this->options->pollOptions = $pollOptions;
        return $this;
    }


}
