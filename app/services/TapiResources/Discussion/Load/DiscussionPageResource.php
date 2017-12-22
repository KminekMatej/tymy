<?php

namespace Tapi;
use Tymy\Exception\APIException;

/**
 * Project: tymy_v2
 * Description of DiscussionPageResource
 *
 * @author kminekmatej created on 8.12.2017, 10:39:17
 */
class DiscussionPageResource extends DiscussionResource {
    
    private $page;
    private $search;
    
    public function init() {
        $this->setCacheable(FALSE);
    }
    
    public function preProcess() {
        if($this->getId() == null) throw new APIException ("Discussion ID is missing");
        if($this->getPage() == null) $this->setPage(1);
        $this->setUrl("discussion/" . $this->getId() . "/html/" . $this->getPage());
        if(!empty($this->search)){
            $this->setRequestParameter("search", $this->search);
        }
        return $this;
    }

    protected function postProcess() {
        parent::postProcessDiscussion($this->data->discussion);
        foreach ($this->data->posts as $post) {
            parent::postProcessDiscussionPost($post);
        }
    }
    
    public function getPage() {
        return $this->page;
    }

    public function setPage($page) {
        $this->page = $page;
        return $this;
    }

    public function getSearch() {
        return $this->search;
    }

    public function setSearch($search) {
        $this->search = $search;
        return $this;
    }

}
