<?php

namespace Tymy\Module\Discussion\Presenter\Front;

use Tymy\Module\Core\Component\NewPostControl;
use Tymy\Module\Core\Exception\TymyResponse;
use Tymy\Module\Core\Presenter\Front\SecuredPresenter;
use Tymy\Module\Discussion\Manager\DiscussionManager;
use Tymy\Module\Discussion\Manager\PostManager;
use Tymy\Module\Discussion\Model\Post;
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
    private array $userList;

    public function beforeRender(): void
    {
        parent::beforeRender();
        $this->addBreadcrumb($this->translator->translate("discussion.discussion", 2), $this->link(":Discussion:Default:"));

        //set users
        $this->template->userList = $this->userList = $this->userManager->getIdList();

        $this->template->addFilter('myReaction', function (Post $post) {
            foreach ($post->getReactions() as $emoji => $userIds) {
                if (in_array($this->user->getId(), $userIds)) {
                    return $emoji;
                }
            }

            return null;
        });

        $this->template->addFilter('displayNames', fn(array $userIds): string => implode(", ", array_map(fn($userId) => $this->userList[$userId]->getCallName(), $userIds)));
    }

    public function handleReact(int $postId, ?string $reaction = null, bool $remove = false): void
    {
        if (empty($reaction)) {
            $this->sendPayload();   //terminate to avoid jumping into render function
        }

        /* @var $post Post */
        $post = $this->postManager->getById($postId);

        $this->postManager->react($post->getDiscussionId(), $postId, $this->user->getId(), $reaction, $remove);

        if (!$this->isAjax()) {
            $this->redirect('this');
        }

        $this->sendPayload();   //terminate to avoid jumping into render function
    }

    public function renderDefault(string $discussion, int $page = 1, ?string $search = null, string $suser = "all", ?string $jump2date = null): void
    {
        $d = (is_int($discussion) || is_numeric($discussion)) ? $this->discussionManager->getById((int) $discussion) : $this->discussionManager->getByWebName($discussion, $this->user->getId());

        if (empty($d)) {
            $this->error($this->translator->translate("discussion.errors.noDiscussionExists"));
        }

        $this->template->search = $search;
        $this->template->suser = $suser;
        $this->template->jump2date = $jump2date;

        $discussionPosts = $this->postManager->mode($d->getId(), $page, "html", $search, $suser, $jump2date);

        $this->addBreadcrumb($d->getCaption(), $this->link(":Discussion:Discussion:", [$d->getWebName()]));

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

    public function actionNewPost(string $discussion): void
    {
        $post = $this->getHttpRequest()->getPost("post");
        $discussionId = (int) $discussion;
        if (!empty($post) && trim($post) != "") {
            $this->postManager->create([
                "post" => $post,
                "discussionId" => $discussionId,
                "createdById" => $this->user->getId(),
            ], $discussionId);
        }
        $this->setView('default');
    }

    public function actionEditPost(string $discussion): void
    {

        $postId = $this->getHttpRequest()->getPost("postId");

        $updates = [];
        if ($this->getHttpRequest()->getPost("post")) {
            $updates["post"] = $this->getHttpRequest()->getPost("post");
        }
        if ($this->getHttpRequest()->getPost("sticky")) {
            $updates["sticky"] = $this->getHttpRequest()->getPost("sticky");
        }

        $discussionId = (int) $discussion;

        try {
            $this->postManager->update($updates, $discussionId, $postId);
        } catch (TymyResponse $tResp) {
            $this->handleTymyResponse($tResp);
        }

        $this->setView('default');
    }

    public function handleDeletePost(?int $postId, int $discussionId, $currentPage): void
    {
        try {
            $this->postManager->delete($discussionId, $postId);
            $this->redirect(":Discussion:Discussion:", ["discussion" => $discussionId, "page" => $currentPage]);
        } catch (TymyResponse $tResp) {
            $this->handleTymyResponse($tResp);
        }
    }

    public function actionStickPost(int $postId, int $discussionId, $sticky): void
    {
        try {
            $this->postManager->stickPost($postId, $discussionId, (bool) $sticky);
            $this->redirect(":Discussion:Discussion:", ["discussion" => $discussionId, "page" => 1]);
        } catch (TymyResponse $tResp) {
            $this->handleTymyResponse($tResp);
        }
    }

    protected function createComponentNewPost(): \Tymy\Module\Core\Component\NewPostControl
    {
        $newpost = new NewPostControl($this->userManager);
        $newpost->redrawControl();
        return $newpost;
    }
}
