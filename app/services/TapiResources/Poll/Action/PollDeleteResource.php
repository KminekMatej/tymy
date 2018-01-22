<?php

namespace Tapi;
use Tapi\Exception\APIException;

/**
 * Project: tymy_v2
 * Description of PollDeleteResource
 *
 * @author kminekmatej created on 5.1.2018, 10:00:09
 */
class PollDeleteResource extends PollResource {
    
    public function init() {
        parent::globalInit();
        $this->setCacheable(FALSE);
        $this->setMethod(RequestMethod::DELETE);
        return $this;
    }

    protected function preProcess() {
        if($this->getId() == null)
            throw new APIException('Poll ID not set');
        
        $this->setUrl("polls/" . $this->getId());
        
        $this->setRequestData((object)["id" => $this->getId()]);
    }

    protected function postProcess() {
        $this->clearCache($this->getId());
    }

}
