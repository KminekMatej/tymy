<?php

namespace Tapi;
use Tapi\Exception\APIException;

/**
 * Project: tymy_v2
 * Description of DiscussionPostEditResource
 *
 * @author kminekmatej created on 8.12.2017, 10:39:17
 */
class DiscussionPostDeleteResource extends DiscussionResource {
    
    public function init() {
        $this->setCacheable(FALSE);
        $this->setMethod(RequestMethod::DELETE);
        $this->setPostId(NULL);
        return $this;
    }
    
    public function preProcess() {
        if($this->getId() == null) throw new APIException ("Discussion ID is missing");
        if($this->getPostId() == null) throw new APIException ("Post ID is missing");
        $this->setUrl("discussion/" . $this->getId() . "/post");
        $this->setRequestData((object)[
            "id" => $this->getPostId()
        ]);
        return $this;
    }

    protected function postProcess() {
        $this->clearCache();
    }
    
    public function getPostId() {
        return $this->options->postId;
    }

    public function setPostId($postId) {
        $this->options->postId = $postId;
        return $this;
    }



}
