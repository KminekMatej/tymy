<?php

namespace Tapi;
use Tymy\Exception\APIException;

/**
 * Project: tymy_v2
 * Description of DiscussionDetail
 *
 * @author kminekmatej created on 8.12.2017, 10:39:17
 */
class DiscussionPosts extends DiscussionResource {
    
    private $mode;
    private $page;
    
    public function init() {
        //everything initializeded in constructor properly
    }
    
    public function composeUrl() {
        if($this->getId() == null) throw new APIException ("Discussion ID is missing");
        if($this->getMode() == null) throw new APIException ("Mode is missing");
        if($this->getPage() == null) throw new APIException ("Page is missing");
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
    
    public function getMode() {
        return $this->mode;
    }

    public function getPage() {
        return $this->page;
    }

    public function setMode($mode) {
        $this->mode = $mode;
        return $this;
    }

    public function setPage($page) {
        $this->page = $page;
        return $this;
    }




}
