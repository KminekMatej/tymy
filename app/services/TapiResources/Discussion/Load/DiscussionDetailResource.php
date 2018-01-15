<?php

namespace Tapi;
use Tapi\Exception\APIException;

/**
 * Project: tymy_v2
 * Description of DiscussionDetail
 *
 * @author kminekmatej created on 8.12.2017, 10:39:17
 */
class DiscussionDetailResource extends DiscussionResource {
    
    protected function init() {
        $this->setCachingTimeout(CacheService::TIMEOUT_LARGE);
    }
    
    public function preProcess() {
        if($this->getId() == null) throw new APIException ("Discussion ID is missing");
        $this->setUrl("discussion/" . $this->getId());
        return $this;
    }

    protected function postProcess() {
        $this->postProcessDiscussion($this->data);
    }


}
