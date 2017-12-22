<?php

namespace Tapi;
use Tymy\Exception\APIException;

/**
 * Project: tymy_v2
 * Description of DiscussionDetail
 *
 * @author kminekmatej created on 8.12.2017, 10:39:17
 */
class DiscussionDetailResource extends DiscussionResource {
    
    public function init() {
        $this->setCachingTimeout(CachedResult::TIMEOUT_LARGE);
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
