<?php

namespace Tapi;
use Tymy\Exception\APIException;

/**
 * Project: tymy_v2
 * Description of DiscussionPostEditResource
 *
 * @author kminekmatej created on 8.12.2017, 10:39:17
 */
class DiscussionPostDeleteResource extends DiscussionResource {
    
    private $postId;
    
    public function init() {
        $this->setCacheable(FALSE);
        $this->setMethod(RequestMethod::DELETE);
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
        return $this->postId;
    }

    public function setPostId($postId) {
        $this->postId = $postId;
        return $this;
    }



}
