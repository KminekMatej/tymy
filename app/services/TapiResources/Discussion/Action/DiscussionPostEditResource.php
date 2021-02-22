<?php

namespace Tapi;
use Tapi\Exception\APIException;

/**
 * Project: tymy_v2
 * Description of DiscussionPostEditResource
 *
 * @author kminekmatej created on 8.12.2017, 10:39:17
 */
class DiscussionPostEditResource extends DiscussionResource {
    
    public function init() {
        parent::globalInit();
        $this->setCacheable(FALSE);
        $this->setMethod(RequestMethod::PUT);
        $this->setPost(NULL);
        $this->setPostId(NULL);
        $this->setSticky(NULL);
        return $this;
    }
    
    public function preProcess() {
        if($this->getId() == null) throw new APIException('Discussion ID is missing', self::BAD_REQUEST);
        if($this->getPostId() == null) throw new APIException('Post ID is missing', self::BAD_REQUEST);
        if($this->options->post == NULL && $this->options->sticky == NULL)
             throw new APIException ("Nothing to update");
        $this->setUrl("discussion/" . $this->getId() . "/post");
        
        $rqData = [
            "id" => $this->getPostId(),
        ];
        if($this->getPost()){
            $rqData["post"] = $this->getPost();
        }
        if ($this->getSticky()) {
            $rqData["sticky"] = $this->getSticky();
        }

        $this->setRequestData((object) $rqData);

        return $this;
    }

    protected function postProcess() {
        $this->clearCache($this->getId());
        parent::postProcessDiscussionPost($this->data);
    }
    
    public function getPost() {
        return $this->options->post;
    }

    public function setPost($post) {
        $this->options->post = $post;
        return $this;
    }
    
    public function getSticky() {
        return $this->options->sticky;
    }

    public function setSticky($sticky) {
        $this->options->sticky = $sticky;
        return $this;
    }

    public function getPostId() {
        return $this->options->postId;
    }

    public function setPostId($postId) {
        $this->options->postId = $postId;
        return $this;
    }


}
