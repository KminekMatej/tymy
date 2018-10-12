<?php

namespace Tapi;
use Tapi\Exception\APIException;

/**
 * Project: tymy_v2
 * Description of OptionCreateResource
 *
 * @author kminekmatej created on 5.1.2018, 15:23:48
 */
class OptionCreateResource extends PollResource {
       
    public function init() {
        parent::globalInit();
        $this->setCacheable(FALSE);
        $this->setMethod(RequestMethod::POST);
        $this->setPollOptions(NULL);
        return $this;
    }

    protected function preProcess() {
        if (!$this->getId())
            throw new APIException('Poll ID not set', self::BAD_REQUEST);
        if (!$this->getPollOptions())
            throw new APIException('Poll option object not set', self::BAD_REQUEST);
        //TODO check correct rights from authorization API
        foreach ($this->getPollOptions() as $option) {
            if(!array_key_exists("caption", $option))
                throw new APIException('Option caption not set', self::BAD_REQUEST);
            if(!array_key_exists("type", $option))
                throw new APIException('Option type not set', self::BAD_REQUEST);
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
