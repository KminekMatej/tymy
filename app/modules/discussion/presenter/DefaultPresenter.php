<?php

namespace Tymy\Api\Module\Discussion\Presenters;

use Tymy\Api\Module\Core\Presenters\SecuredPresenter;
use Tymy\Module\Discussion\Manager\DiscussionManager;

/**
 * Description of DefaultPresenter
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 13. 9. 2020
 */
class DefaultPresenter extends SecuredPresenter
{
    public function injectManager(DiscussionManager $discussionManager)
    {
        $this->manager = $discussionManager;
    }

    public function actionDefault($resourceId, $subResourceId)
    {
        if (empty($resourceId) && in_array($this->getRequest()->getMethod(), ["PUT", "DELETE"])) {
            $resourceId = $this->requestData["id"];
        }

        switch ($this->getRequest()->getMethod()) {
            case 'GET':
                $resourceId ? $this->requestGet($resourceId, $subResourceId) : $this->requestGetList();
                // no break
            case 'POST':
                $this->requestPost($resourceId);
                // no break
            case 'PUT':
                $this->needs($resourceId);
                $this->requestPut($resourceId, $subResourceId);
                // no break
            case 'DELETE':
                $this->needs($resourceId);
                $this->requestDelete($resourceId, $subResourceId);
        }

        $this->respondNotAllowed();
    }

    private function requestGetList()
    {
        $discussions = $this->manager->getListUserAllowed($this->user->getId());

        $this->respondOk($this->arrayToJson($discussions));
    }
}
