<?php

namespace Tymy\Module\Discussion\Presenter\Api;

use Tymy\Module\Core\Presenter\Api\SecuredPresenter;
use Tymy\Module\Discussion\Manager\DiscussionManager;

/**
 * Description of DefaultPresenter
 */
class DefaultPresenter extends SecuredPresenter
{
    public function injectManager(DiscussionManager $discussionManager): void
    {
        $this->manager = $discussionManager;
    }

    public function actionDefault($resourceId, $subResourceId): void
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

    private function requestGetList(): void
    {
        assert($this->manager instanceof DiscussionManager);
        $discussions = $this->manager->getListUserAllowed($this->user->getId());

        $this->respondOk($this->arrayToJson($discussions));
    }
}
