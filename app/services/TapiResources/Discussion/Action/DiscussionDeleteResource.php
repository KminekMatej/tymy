<?php

namespace Tapi;
use Tapi\Exception\APIException;

/**
 * Project: tymy_v2
 * Description of DiscussionDeleteResource
 *
 * @author kminekmatej created on 29.12.2017, 9:09:26
 */
class DiscussionDeleteResource extends DiscussionResource {

    public function init() {
        parent::globalInit();
        $this->setCacheable(FALSE);
        $this->setMethod(RequestMethod::DELETE);
        return $this;
    }

    protected function preProcess() {
        if($this->getId() == null)
            throw new APIException('Discussion ID not set');
        
        $this->setUrl("discussions");
        
        $this->setRequestData((object)["id" => $this->getId()]);
    }

    protected function postProcess() {
        $this->clearCache($this->getId());
    }

}
