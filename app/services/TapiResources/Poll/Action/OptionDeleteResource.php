<?php

namespace Tapi;

/**
 * Project: tymy_v2
 * Description of OptionDeleteResource
 *
 * @author kminekmatej created on 5.1.2018, 15:24:17
 */
class OptionDeleteResource extends PollResource{
   
    protected function init() {
        $this->setCacheable(FALSE);
        $this->setMethod(RequestMethod::DELETE);
    }

    protected function preProcess() {
        
    }
    
    protected function postProcess() {
        
    }
    
}
