<?php

namespace Tapi;
use Tymy\Exception\APIException;

/**
 * Project: tymy_v2
 * Description of DiscussionEditResource
 *
 * @author kminekmatej created on 29.12.2017, 9:09:52
 */
class DiscussionEditResource extends DiscussionResource {

    private $discussion;
    
    public function init() {
        $this->setCacheable(FALSE);
        $this->setMethod(RequestMethod::PUT);
    }

    protected function preProcess() {
        if($this->discussion == null)
            throw new APIException('Discussion not set!');
        if($this->getId() == null)
            throw new APIException('Discussion ID not set!');
        
        $this->setUrl("discussions");
        $this->discussion["id"] = $this->getId();
        $this->setRequestData($this->discussion);
    }

    protected function postProcess() {
        $this->clearCache($this->getId());
    }
    
    public function getDiscussion() {
        return $this->discussion;
    }

    public function setDiscussion($discussion) {
        $this->discussion = $discussion;
        return $this;
    }

}
