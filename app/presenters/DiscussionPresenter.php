<?php

namespace App\Presenters;

use Nette\Application\UI\NewPostControl;
use Tapi\Exception\APIException;
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
        try{
            $this->template->discussions = $this->discussionsList->init()->getData();
        } catch (APIException $ex){
            $this->handleTapiException($ex);
        }
    }
    
    public function actionNewPost($discussion){
        $post = $this->getHttpRequest()->getPost("post");
        if (trim($post) != "") {
            try {
                $this->discussionPostCreate->init()
                        ->setId($discussion)
                        ->setPost($post)
                        ->perform();
            } catch (APIException $ex) {
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
            $this->discussionPostEdit->init()
                        ->setId($discussion)
                        ->setPostId($postId)
                        ->setPost($text)
                        ->setSticky($sticky)
                        ->perform();
        } catch (APIException $ex) {
            $this->handleTapiException($ex, 'this');
        }
        $this->setView('discussion');
    }
    
    public function actionDeletePost($discussion) {
        $postId = $this->getHttpRequest()->getPost("postId");
        try {
            $this->discussionPostDelete->init()
                        ->setId($discussion)
                        ->setPostId($postId)
                        ->perform();
        } catch (APIException $ex) {
            $this->handleTapiException($ex, 'this');
        }
        $this->setView('discussion');
    }

    public function actionStickPost($discussion){
        $postId = $this->getHttpRequest()->getPost("postId");
        $sticky = $this->getHttpRequest()->getPost("sticky");
        try {
            $this->discussionPostEdit->init()
                        ->setId($discussion)
                        ->setPostId($postId)
                        ->setSticky($sticky)
                        ->perform();
        } catch (APIException $ex) {
            $this->handleTapiException($ex);
        }
        $this->setView('discussion');
    }
    
    public function renderDiscussion($discussion, $page, $search, $suser = "all", $jump2date = "") {
        $discussionId = DiscussionResource::getIdFromWebname($discussion, $this->discussionsList->init()->getData());
        
        if (is_null($discussionId) || $discussionId < 1)
            $this->error("Tato diskuze neexistuje");
        $this->discussionPage->init();
        
        $this->discussionPage
                ->setId($discussionId)
                ->setPage($page);
        
        $this->template->search = "";
        $this->template->suser = 0;
        if($search){
            $this->discussionPage->setSearch($search);
            $this->template->search = $search;
        }
        if ($suser && $suser != "all") {
            $this->discussionPage->setSearchUser($suser);
            $this->template->suser = $suser;
        }
        if ($jump2date) {
            $this->discussionPage->setJumpDate($jump2date);
            $this->template->jump2date = $this->discussionPage->getJumpDate(); // getter returns already formatted value
        }


        try {
            $data = $this->discussionPage->getData();
            $this->userList->init()->getData();
            $this->template->userList = $this->userList->getById();
        } catch (APIException $ex) {
            $this->handleTapiException($ex);
        }
        
        $this->setLevelCaptions(["2" => ["caption" => $data->discussion->caption, "link" => $this->link("Discussion:discussion", [$data->discussion->webName]) ] ]);
        
        $this->template->userId = $this->getUser()->getId();
        $this->template->discussion = $data;
        $this->template->nazevDiskuze = $data->discussion->webName;
        $currentPage = is_numeric($data->paging->currentPage) ? $data->paging->currentPage : 1 ;
        $this->template->currentPage = $currentPage;
        $lastPage = is_numeric($data->paging->numberOfPages) ? $data->paging->numberOfPages : 1 ;
        $this->template->lastPage = $lastPage;
        $this->template->pagination = $this->pagination($lastPage, 1, $currentPage, 5);
        if($this->isAjax())
            $this->redrawControl("discussion");
    }
    
    protected function createComponentNewPost($discussion) {
        $newpost = new NewPostControl($discussion);
        $newpost->setUserList($this->userList);
        $newpost->redrawControl();
        return $newpost;
    }
     
}
