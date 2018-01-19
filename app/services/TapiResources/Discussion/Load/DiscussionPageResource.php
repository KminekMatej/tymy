<?php

namespace Tapi;
use Tapi\Exception\APIException;

/**
 * Project: tymy_v2
 * Description of DiscussionPageResource
 *
 * @author kminekmatej created on 8.12.2017, 10:39:17
 */
class DiscussionPageResource extends DiscussionResource {
    
    public function init() {
        parent::globalInit();
        $this->setCacheable(FALSE);
        $this->setPage(NULL);
        $this->setSearch(NULL);
        return $this;
    }
    
    public function preProcess() {
        if($this->getId() == null) throw new APIException ("Discussion ID is missing");
        if($this->getPage() == null) $this->setPage(1);
        $this->setUrl("discussion/" . $this->getId() . "/html/" . $this->getPage());
        if(!empty($this->options->search)){
            $this->setRequestParameter("search", $this->options->search);
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
        return $this->options->page;
    }

    public function setPage($page) {
        $this->options->page = is_numeric($page) ? $page : 1 ;
        return $this;
    }

    public function getSearch() {
        return $this->options->search;
    }

    public function setSearch($search) {
        $this->options->search = $search;
        return $this;
    }

}
