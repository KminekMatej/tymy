<?php

namespace Tapi;
use Tapi\Exception\APIException;

/**
 * Project: tymy_v2
 * Description of DiscussionCreateResource
 *
 * @author kminekmatej created on 29.12.2017, 9:09:36
 */
class DiscussionCreateResource extends DiscussionResource {

    public function init() {
        parent::globalInit();
        $this->setCacheable(FALSE);
        $this->setMethod(RequestMethod::POST);
        $this->setDiscussion(NULL);
        return $this;
    }

    protected function preProcess() {
        if($this->getDiscussion() == null)
            throw new APIException('Discussion object is missing', self::BAD_REQUEST);
        
        $this->setUrl("discussions");
        $this->setRequestData($this->getDiscussion());
    }

    protected function postProcess() {
        $this->clearCache();
    }
    
    public function getDiscussion() {
        return $this->options->discussion;
    }

    public function setDiscussion($discussion) {
        $this->options->discussion = $discussion;
        return $this;
    }



}
