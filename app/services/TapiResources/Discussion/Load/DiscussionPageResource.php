<?php

namespace Tapi;
use Tapi\Exception\APIException;
use Nette\Utils\DateTime;

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
        $this->setSearchUser(NULL);
        $this->setJumpDate(NULL);
        return $this;
    }
    
    public function preProcess() {
        if($this->getId() == null) throw new APIException('Discussion ID is missing', self::BAD_REQUEST);
        if($this->getPage() == null) $this->setPage(1);
        $this->setUrl("discussion/" . $this->getId() . "/html/" . $this->getPage());
        if (!empty($this->options->search)) {
            $this->setRequestParameter("search", $this->options->search);
        }
        if (!empty($this->options->searchUser)) {
            $this->setRequestParameter("suser", $this->options->searchUser);
        }
        if (!empty($this->options->jumpDate)) {
            $this->setRequestParameter("jump2date", $this->options->jumpDate);
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

    public function getSearchUser() {
        return $this->options->searchUser;
    }

    public function setSearchUser($user) {
        $this->options->searchUser = $user;
        return $this;
    }

    public function getJumpDate() {
        return $this->options->jumpDate;
    }

    public function setJumpDate($jumpDate) {
        if($jumpDate != NULL && $date = strtotime($jumpDate) !== FALSE){
            $this->options->jumpDate = date("Y-m-d", $date);
        } else $this->options->jumpDate = NULL;
        return $this;
    }

}
