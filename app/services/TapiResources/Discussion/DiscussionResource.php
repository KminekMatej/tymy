<?php

namespace Tapi;
use Tapi\TapiAbstraction;

/**
 * Description of AttendanceResource
 *
 * @author kminekmatej
 */
abstract class DiscussionResource extends TapiAbstraction {
    
    protected function postProcess() {
        if (($data = $this->getData()) == null){
            return;
        }
            
    }
    
}
