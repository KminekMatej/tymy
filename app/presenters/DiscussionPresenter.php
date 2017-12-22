<?php

namespace App\Presenters;

use Nette\Application\UI\NewPostControl;
use Tapi\DiscussionDetailResource;
use Tapi\DiscussionListResource;
use Tapi\DiscussionNewsListResource;
use Tapi\DiscussionPageResource;
use Tapi\DiscussionPostCreateResource;
use Tapi\DiscussionPostDeleteResource;
use Tapi\DiscussionPostEditResource;
use Tapi\DiscussionResource;

/**
 * Description of DiscussionPresenter
 *
 * @author matej
 */
class DiscussionPresenter extends SecuredPresenter {
    
    /** @var DiscussionPageResource @inject */
    public $discussionPage;

    /** @var DiscussionListResource @inject */
    public $discussionsList;
    
    /** @var DiscussionPostCreateResource @inject */
    public $discussionPostCreate;
    
    /** @var DiscussionPostEditResource @inject */
    public $discussionPostEdit;
    
    /** @var DiscussionPostDeleteResource @inject */
    public $discussionPostDelete;
    

    public function __construct() {
        parent::__construct();
    }
    
    public function startup() {
        parent::startup();
        $this->setLevelCaptions(["1" => ["caption" => "Diskuze", "link" => $this->link("Discussion:")]]);
    }

    public function renderDefault() {
        \Tapi\TapiAbstraction::dumpCache($this->session);
        try{
            $this->template->discussions = $this->discussionsList->getData();
        } catch (\Tymy\Exception\APIException $ex){
            $this->handleTapiException($ex);
        }
    }
    
    public function actionNewPost($discussion){
        $post = $this->getHttpRequest()->getPost("post");
        if (trim($post) != "") {
            try {
                $this->discussionPostCreate
                        ->setId($discussion)
                        ->setPost($post)
                        ->perform();
            } catch (\Tymy\Exception\APIException $ex) {
                $this->handleTapiException($ex, 'this');
            }
        }
        $this->setView('discussion');
    }

    public function actionEditPost($discussion) {
        $postId = $this->getHttpRequest()->getPost("postId");
        $text = $this->getHttpRequest()->getPost("post");
        $sticky = $this->getHttpRequest()->getPost("sticky");
        try {
            $this->discussionPostEdit
                        ->setId($discussion)
                        ->setPostId($postId)
                        ->setPost($text)
                        ->setSticky($sticky)
                        ->perform();
        } catch (\Tymy\Exception\APIException $ex) {
            $this->handleTapiException($ex, 'this');
        }
        $this->setView('discussion');
    }
    
    public function actionDeletePost($discussion) {
        $postId = $this->getHttpRequest()->getPost("postId");
        try {
            $this->discussionPostDelete
                        ->setId($discussion)
                        ->setPostId($postId)
                        ->perform();
        } catch (\Tymy\Exception\APIException $ex) {
            $this->handleTapiException($ex, 'this');
        }
        $this->setView('discussion');
    }

    public function actionStickPost($discussion){
        $postId = $this->getHttpRequest()->getPost("postId");
        $sticky = $this->getHttpRequest()->getPost("sticky");
        try {
            $this->discussionPostEdit
                        ->setId($discussion)
                        ->setPostId($postId)
                        ->setSticky($sticky)
                        ->perform();
        } catch (\Tymy\Exception\APIException $ex) {
            $this->handleTapiException($ex);
        }
        $this->setView('discussion');
    }
    
    public function renderDiscussion($discussion, $page, $search) {
        $discussionId = DiscussionResource::getIdFromWebname($discussion, $this->discussionsList->getData());
        
        if (is_null($discussionId) || $discussionId < 1)
            $this->error("Tato diskuze neexistuje");
        
        $this->discussionPage
                ->setId($discussionId)
                ->setPage($page);
        
        $this->template->search = "";
        if($search){
            $this->discussionPage->setSearch($search);
            $this->template->search = $search;
        }
            
        try {
            $data = $this->discussionPage->getData();
            $this->template->users = $this->users->getData();
        } catch (\Tymy\Exception\APIException $ex) {
            $this->handleTapiException($ex);
        }
        
        $this->setLevelCaptions(["2" => ["caption" => $data->discussion->caption, "link" => $this->link("Discussion:discussion", [$data->discussion->webName]) ] ]);
        
        $this->template->userId = $this->getUser()->getId();
        $this->template->discussion = $data;
        $this->template->nazevDiskuze = $data->discussion->webName;
        $this->template->currentPage = is_numeric($page) ? $page : 1 ;
        $currentPage = is_numeric($page) ? $page : 1;
        $lastPage = is_numeric($data->paging->numberOfPages) ? $data->paging->numberOfPages : 1 ;
        $this->template->lastPage = $lastPage;
        $this->template->pagination = $this->pagination($lastPage, 1, $currentPage, 5);
        if($this->isAjax())
            $this->redrawControl("discussion");
    }
    
    protected function createComponentNewPost($discussion) {
        $newpost = new NewPostControl($discussion);
        $newpost->redrawControl();
        return $newpost;
    }
     
}
