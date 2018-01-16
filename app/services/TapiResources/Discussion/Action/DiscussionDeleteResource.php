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
        $this->setCacheable(FALSE);
        $this->setMethod(RequestMethod::DELETE);
    }

    protected function preProcess() {
        if($this->getId() == null)
            throw new APIException('Discussion ID not set!');
        
        $this->setUrl("discussions/" . $this->getId());
        
        $this->setRequestData((object)["id" => $this->getId()]);
    }

    protected function postProcess() {
        $this->clearCache($this->getId());
    }

}
