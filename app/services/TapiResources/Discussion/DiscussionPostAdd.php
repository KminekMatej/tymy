<?php

namespace Tapi;
use Tymy\Exception\APIException;

/**
 * Project: tymy_v2
 * Description of DiscussionDetail
 *
 * @author kminekmatej created on 8.12.2017, 10:39:17
 */
class DiscussionPostAdd extends DiscussionResource {
    
    private $post;
        
    public function init() {
        $this->setCacheable(FALSE);
        $this->setMethod(RequestMethod::POST);
    }
    
    public function composeUrl() {
        if($this->getId() == null) throw new APIException ("Discussion ID is missing");
        if($this->getPost() == null) throw new APIException ("Post is missing");
        $this->setUrl("discussion/" . $this->getId() . "/" . $this->getMode() . "/" . $this->getPage());
        return $this;
    }

    protected function postProcess() {
        parent::postProcessDiscussion($this->getData());
        $this->timeLoad($this->getData()->updatedAt);
        $this->timeLoad($this->getData()->newInfo->lastVisit);
        foreach ($this->getData()->posts as $post) {
            $this->timeLoad($post->createdAt);
            if (property_exists($post, "updatedAt")) {
                $this->timeLoad($post->updatedAt);
            }
        }
    }
    
    public function getPost() {
        return $this->post;
    }

    public function setPost($post) {
        $this->post = $post;
        return $this;
    }

}
