<?php

namespace Tapi;

/**
 * Project: tymy_v2
 * Description of OptionEditResource
 *
 * @author kminekmatej created on 5.1.2018, 15:23:59
 */
class OptionEditResource extends PollResource{
   
    protected function init() {
        $this->setCacheable(FALSE);
        $this->setMethod(RequestMethod::PUT);
    }

    protected function preProcess() {
        
    }
    
    protected function postProcess() {
        
    }
    
}
