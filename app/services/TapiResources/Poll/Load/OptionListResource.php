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
   
    public function init() {
        parent::globalInit();
        //everything inited correctly
        return $this;
    }

    protected function preProcess() {
        if (!$this->getId())
            throw new APIException('Poll ID is missing', self::BAD_REQUEST);
        
        $this->setUrl("polls/" . $this->getId() . "/options");
        
        return $this;
    }
    
    protected function postProcess() {
        
    }
    
}
