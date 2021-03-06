<?php

namespace Tapi;
use Tapi\Exception\APIException;

/**
 * Project: tymy_v2
 * Description of DiscussionEditResource
 *
 * @author kminekmatej created on 29.12.2017, 9:09:52
 */
class DiscussionEditResource extends DiscussionResource {

    public function init() {
        parent::globalInit();
        $this->setCacheable(FALSE);
        $this->setMethod(RequestMethod::PUT);
        $this->setDiscussion(NULL);
        return $this;
    }

    protected function preProcess() {
        if($this->getId() == null)
            throw new APIException('Discussion ID is missing', self::BAD_REQUEST);
        if($this->getDiscussion() == null)
            throw new APIException('Discussion object is missing', self::BAD_REQUEST);
        
        $this->setUrl("discussions");
        $this->options->discussion["id"] = $this->getId();
        $this->setRequestData($this->getDiscussion());
    }

    protected function postProcess() {
        $this->clearCache($this->getId());
    }
    
    public function getDiscussion() {
        return $this->options->discussion;
    }

    public function setDiscussion($discussion) {
        $this->options->discussion = $discussion;
        return $this;
    }

}
