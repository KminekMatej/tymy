<?php

namespace Tymy\App\Presenters;

use Nette\Application\UI\NewPostControl;
use Tapi\Exception\APIException;
use Tymy\Module\Discussion\Manager\DiscussionManager;
use Tymy\Module\Discussion\Manager\PostManager;
use Tymy\Module\User\Manager\UserManager;

/**
 * Description of DiscussionPresenter
 *
 * @author matej
 */
class DiscussionPresenter extends SecuredPresenter {
    public $discussionPage;
    public $discussionsList;
    public $discussionPostCreate;
    public $discussionPostEdit;
    public $discussionPostDelete;
    
    /** @inject */
    public DiscussionManager $discussionManager;
    
    /** @inject */
    public PostManager $postManager;
    
    /** @inject */
    public UserManager $userManager;

    public function __construct() {
        parent::__construct();
    }
    
    public function startup() {
        parent::startup();
        $this->setLevelCaptions(["1" => ["caption" => $this->translator->translate("discussion.discussion", 2), "link" => $this->link("Discussion:")]]);
    }

    public function renderDefault()
    {
        $this->template->discussions = $this->discussionManager->getListUserAllowed($this->user->getId());
    }

    public function actionNewPost($discussion){
        $post = $this->getHttpRequest()->getPost("post");
        if (trim($post) != "") {
            $this->postManager->createByArray([
                "post" => $post,
                "discussionId" => $discussion,
                "createdById" => $this->user->getId(),
            ]);
        }
        $this->setView('discussion');
    }

    public function actionEditPost($discussion) {
        $postId = $this->getHttpRequest()->getPost("postId");
        $text = $this->getHttpRequest()->getPost("post");
        $sticky = $this->getHttpRequest()->getPost("sticky") ;
        try {
            $this->discussionPostEdit->init()
                        ->setId($discussion)
                        ->setPostId($postId)
                        ->setPost($text)
                        ->setSticky($sticky ? $sticky == "true" : null) //super cool determining if sticky is true, false or null
                        ->perform();
        } catch (APIException $ex) {
            $this->handleTapiException($ex, 'this');
        }
        $this->setView('discussion');
    }

    public function actionDeletePost($postId, $discussionId, $currentPage)
    {
        $this->postManager->delete($discussionId, $postId);

        $this->redirect("Discussion:discussion", ["discussion" => $discussionId, "page" => $currentPage]);
    }

    public function actionStickPost($postId, $discussionId, $sticky)
    {
        $this->postManager->stickPost($postId, $discussionId, $sticky ? true : false);
        
        $this->redirect("Discussion:discussion", ["discussion" => $discussionId, "page" => 1]);
    }

    public function renderDiscussion($discussion, $page, $search, $suser = "all", $jump2date = "") {
        
        $d = (is_int($discussion) || is_numeric($discussion)) ? $this->discussionManager->getById(intval($discussion)) : $this->discussionManager->getByWebName($this->user->getId(), $discussion);

        if (empty($d)){
            $this->error($this->translator->translate("discussion.errors.noDiscussionExists"));
        }  
        
        $this->template->search = $search;
        $this->template->suser = $suser;
        $this->template->jump2date = $jump2date;
        
        $discussionPosts = $this->postManager->mode($d->getId(), $page, "html", $search, $suser, $jump2date);
        
        //set users
        $this->template->userList = $this->userManager->getList();
        
        $this->setLevelCaptions(["2" => ["caption" => $d->getCaption(), "link" => $this->link("Discussion:discussion", [$d->getWebName()]) ] ]);
        
        $this->template->userId = $this->getUser()->getId();
        $this->template->discussionPosts = $discussionPosts;
        $this->template->nazevDiskuze = $discussionPosts->getDiscussion()->getWebName();
        $currentPage = is_numeric($discussionPosts->getCurrentPage()) ? $discussionPosts->getCurrentPage() : 1 ;
        $this->template->currentPage = $currentPage;
        $lastPage = is_numeric($discussionPosts->getNumberOfPages()) ? $discussionPosts->getNumberOfPages() : 1;
        $this->template->lastPage = $lastPage;
        $this->template->pagination = $this->pagination($lastPage, 1, $currentPage, 5);
        if ($this->isAjax()) {
            $this->redrawControl("discussion");
        }
    }

    protected function createComponentNewPost() {
        $newpost = new NewPostControl($this->userManager);
        $newpost->redrawControl();
        return $newpost;
    }
     
}
