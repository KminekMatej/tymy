<?php

namespace Tapi;
use Tapi\Exception\APIException;

/**
 * Project: tymy_v2
 * Description of OptionEditResource
 *
 * @author kminekmatej created on 5.1.2018, 15:23:59
 */
class OptionEditResource extends PollResource{
   
    public function init() {
        parent::globalInit();
        $this->setCacheable(FALSE);
        $this->setMethod(RequestMethod::PUT);
        $this->setOption(NULL);
        return $this;
    }

    protected function preProcess() {
        if($this->getId() == null)
            throw new APIException('Poll ID is missing', self::BAD_REQUEST);
        if($this->getOption() == null)
            throw new APIException('Option object is missing', self::BAD_REQUEST);
        
        $this->setUrl("polls/" . $this->getId() . "/options");
        $this->setRequestData($this->getOption());
    }
    
    protected function postProcess() {
        $this->clearCache($this->getId());
    }
    
    public function getOption() {
        return $this->options->option;
    }

    public function setOption($option) {
        $this->options->option = $option;
        return $this;
    }
    
}
