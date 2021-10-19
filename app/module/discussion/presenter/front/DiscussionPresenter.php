<?php

namespace Tymy\Module\Discussion\Presenter\Front;

use Tymy\Module\Core\Component\NewPostControl;
use Tymy\Module\Core\Presenter\Front\SecuredPresenter;
use Tymy\Module\Discussion\Manager\DiscussionManager;
use Tymy\Module\Discussion\Manager\PostManager;
use Tymy\Module\User\Manager\UserManager;

/**
 * Description of DiscussionPresenter
 *
 * @author matej
 */
class DiscussionPresenter extends SecuredPresenter
{

    /** @inject */
    public DiscussionManager $discussionManager;

    /** @inject */
    public PostManager $postManager;

    /** @inject */
    public UserManager $userManager;

    public function beforeRender()
    {
        parent::beforeRender();
        $this->setLevelCaptions(["1" => ["caption" => $this->translator->translate("discussion.discussion", 2), "link" => $this->link(":Discussion:Default:")]]);
    }

    public function renderDefault(string $discussion, int $page = 1, ?string $search = null, string $suser = "all", ?string $jump2date = null)
    {
        $d = (is_int($discussion) || is_numeric($discussion)) ? $this->discussionManager->getById(intval($discussion)) : $this->discussionManager->getByWebName($discussion, $this->user->getId());

        if (empty($d)) {
            $this->error($this->translator->translate("discussion.errors.noDiscussionExists"));
        }

        $this->template->search = $search;
        $this->template->suser = $suser;
        $this->template->jump2date = $jump2date;

        $discussionPosts = $this->postManager->mode($d->getId(), $page, "html", $search, $suser, $jump2date);

        //set users
        $this->template->userList = $this->userManager->getList();

        $this->setLevelCaptions(["2" => ["caption" => $d->getCaption(), "link" => $this->link(":Discussion:Discussion:", [$d->getWebName()])]]);

        $this->template->userId = $this->getUser()->getId();
        $this->template->discussionPosts = $discussionPosts;
        $this->template->nazevDiskuze = $discussionPosts->getDiscussion()->getWebName();
        $currentPage = is_numeric($discussionPosts->getCurrentPage()) ? $discussionPosts->getCurrentPage() : 1;
        $this->template->currentPage = $currentPage;
        $lastPage = is_numeric($discussionPosts->getNumberOfPages()) ? $discussionPosts->getNumberOfPages() : 1;
        $this->template->lastPage = $lastPage;
        $this->template->pagination = $this->pagination($lastPage, 1, $currentPage, 5);
        if ($this->isAjax()) {
            $this->redrawControl("discussion");
        }
    }

    public function actionNewPost(string $discussion)
    {
        $post = $this->getHttpRequest()->getPost("post");
        if (trim($post) != "") {
            $this->postManager->create([
                "post" => $post,
                "discussionId" => $discussion,
                "createdById" => $this->user->getId(),
            ]);
        }
        $this->setView('discussion');
    }

    public function actionEditPost(string $discussion)
    {
        $postId = $this->getHttpRequest()->getPost("postId");
        $text = $this->getHttpRequest()->getPost("post");
        $sticky = $this->getHttpRequest()->getPost("sticky");

        $this->postManager->update([
            "post" => $text,
            "sticky" => $sticky,
                ], $discussion, $postId);

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

    protected function createComponentNewPost()
    {
        $newpost = new NewPostControl($this->userManager);
        $newpost->redrawControl();
        return $newpost;
    }

}