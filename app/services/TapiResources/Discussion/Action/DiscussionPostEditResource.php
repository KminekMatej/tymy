<?php

namespace Tapi;
use Tymy\Exception\APIException;

/**
 * Project: tymy_v2
 * Description of DiscussionPostEditResource
 *
 * @author kminekmatej created on 8.12.2017, 10:39:17
 */
class DiscussionPostEditResource extends DiscussionResource {
    
    private $post;
    private $sticky;
    private $postId;
    
        
    public function init() {
        $this->setCacheable(FALSE);
        $this->setMethod(RequestMethod::PUT);
    }
    
    public function preProcess() {
        if($this->getId() == null) throw new APIException ("Discussion ID is missing");
        if($this->getPostId() == null) throw new APIException ("Post ID is missing");
        if($this->post == NULL && $this->sticky == NULL)
            return null;
        $this->setUrl("discussion/" . $this->getId() . "/post");
        
        $this->setRequestData((object)[
            "id" => $this->getPostId(),
            "post" => $this->getPost(),
            "sticky" => $this->getSticky(),
        ]);
        
        return $this;
    }

    protected function postProcess() {
        $this->clearCache($this->getId());
        parent::postProcessDiscussionPost($this->data);
    }
    
    public function getPost() {
        return $this->post;
    }

    public function setPost($post) {
        $this->post = $post;
        return $this;
    }
    
    public function getSticky() {
        return $this->sticky;
    }

    public function setSticky($sticky) {
        $this->sticky = $sticky;
        return $this;
    }

    public function getPostId() {
        return $this->postId;
    }

    public function setPostId($postId) {
        $this->postId = $postId;
        return $this;
    }


}
