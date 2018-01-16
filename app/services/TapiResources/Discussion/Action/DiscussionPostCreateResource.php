<?php

namespace Tapi;
use Tapi\Exception\APIException;

/**
 * Project: tymy_v2
 * Description of DiscussionPostCreateResource
 *
 * @author kminekmatej created on 8.12.2017, 10:39:17
 */
class DiscussionPostCreateResource extends DiscussionResource {
    
    public function init() {
        $this->setCacheable(FALSE);
        $this->setMethod(RequestMethod::POST);
        $this->setPost(NULL);
    }
    
    public function preProcess() {
        if($this->getId() == null) throw new APIException ("Discussion ID is missing");
        if($this->getPost() == null) throw new APIException ("Post is missing");
        $this->setUrl("discussion/" . $this->getId() . "/post");
        $this->setRequestData((object)[
            "post" => $this->getPost(),
        ]);
        return $this;
    }

    protected function postProcess() {
        $this->clearCache();
        parent::postProcessDiscussionPost($this->data);
    }
    
    public function getPost() {
        return $this->options->post;
    }

    public function setPost($post) {
        $this->options->post = $post;
        return $this;
    }

}
