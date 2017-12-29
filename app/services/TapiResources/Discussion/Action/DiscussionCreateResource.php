<?php

namespace Tapi;
use Tymy\Exception\APIException;

/**
 * Project: tymy_v2
 * Description of DiscussionCreateResource
 *
 * @author kminekmatej created on 29.12.2017, 9:09:36
 */
class DiscussionCreateResource extends DiscussionResource {

    private $discussion;
    
    public function init() {
        $this->setCacheable(FALSE);
        $this->setMethod(RequestMethod::POST);
    }

    protected function preProcess() {
        if($this->getDiscussion() == null)
            throw new APIException('Discussion not set!');
        
        $this->setUrl("discussions");
        $this->setRequestData($this->getDiscussion());
        
    }

    protected function postProcess() {
        $this->clearCache();
    }
    
    public function getDiscussion() {
        return $this->discussion;
    }

    public function setDiscussion($discussion) {
        $this->discussion = $discussion;
        return $this;
    }



}
