<?php

namespace Tymy\Api\Module\Discussion\Presenters;

use Tymy\Api\Module\Core\Presenters\SecuredPresenter;
use Tymy\Module\Discussion\Manager\DiscussionManager;

/**
 * Description of NewOnlyPresenter
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 16. 9. 2020
 */
class NewOnlyPresenter extends SecuredPresenter
{
    public function injectDiscussionManager(DiscussionManager $discussionManager)
    {
        $this->manager = $discussionManager;
    }

    public function actionDefault()
    {
        if ($this->getRequest()->getMethod() !== "GET") {
            $this->respondNotAllowed();
        }

        $this->requestGet();
    }

    protected function requestGet($resourceId = null, $subResourceId = null)
    {
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
