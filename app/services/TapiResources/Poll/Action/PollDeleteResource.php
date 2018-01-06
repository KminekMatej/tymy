<?php

namespace Tapi;
use Tymy\Exception\APIException;

/**
 * Project: tymy_v2
 * Description of PollDeleteResource
 *
 * @author kminekmatej created on 5.1.2018, 10:00:09
 */
class PollDeleteResource extends PollResource {
    
    protected function init() {
        $this->setCacheable(FALSE);
        $this->setMethod(RequestMethod::DELETE);
    }

    protected function preProcess() {
        if($this->getId() == null)
            throw new APIException('Poll ID not set!');
        
        $this->setUrl("polls/" . $this->getId());
        
        $this->setRequestData((object)["id" => $this->getId()]);
    }

    protected function postProcess() {
        $this->clearCache($this->getId());
    }

}
