<?php

namespace Tapi;
use Tapi\Exception\APIException;

/**
 * Project: tymy_v2
 * Description of OptionDetailResource
 *
 * @author kminekmatej created on 5.1.2018, 15:23:33
 */
class OptionListResource extends PollResource{
   
    protected function init() {
        //everything inited correctly
    }

    protected function preProcess() {
        if (!$this->getId())
            throw new APIException('Poll ID not set!');
        
        $this->setUrl("polls/" . $this->getId() . "/options");
        
        return $this;
    }
    
    protected function postProcess() {
        
    }
    
}
