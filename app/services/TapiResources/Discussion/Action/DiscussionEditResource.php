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
        if($this->getDiscussion() == null)
            throw new APIException('Discussion not set!');
        if(empty($this->getDiscussion()->id))
            throw new APIException('Discussion ID not set!');
        
        $this->setUrl("discussions");
        $this->setRequestData($this->getDiscussion());  
    }

    protected function postProcess() {
        $this->clearCache($this->data->id);
    }
    
    public function getDiscussion() {
        return $this->discussion;
    }

    public function setDiscussion($discussion) {
        $this->discussion = $discussion;
        return $this;
    }

}
