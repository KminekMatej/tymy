<?php

namespace Tymy\Module\Discussion\Presenter\Api;

use Tymy\Module\Core\Presenter\Api\SecuredPresenter;
use Tymy\Module\Discussion\Manager\DiscussionManager;

/**
 * Description of NewOnlyPresenter
 */
class NewOnlyPresenter extends SecuredPresenter
{
    public function injectDiscussionManager(DiscussionManager $discussionManager): void
    {
        $this->manager = $discussionManager;
    }

    public function actionDefault(): void
    {
        if ($this->getRequest()->getMethod() !== "GET") {
            $this->respondNotAllowed();
        }

        $this->requestGet();
    }

    protected function requestGet($resourceId = null, $subResourceId = null): void
    {
        assert($this->manager instanceof DiscussionManager);
        $discussions = $this->manager->getListUserAllowed($this->user->getId());
        $output = [];

        foreach ($discussions as $discussion) {
            $output[] = [
                "id" => $discussion->getId(),
                "newPosts" => $discussion->getNewInfo()->getNewsCount(),
            ];
        }

        $this->respondOk($output);
    }
}
