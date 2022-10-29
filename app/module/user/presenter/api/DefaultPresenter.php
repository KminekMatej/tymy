<?php

namespace Tymy\Module\User\Presenter\Api;

use Tymy\Module\Core\Presenter\Api\SecuredPresenter;
use Tymy\Module\User\Manager\UserManager;

/**
 * Description of DefaultPresenter
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 28. 8. 2020
 */
class DefaultPresenter extends SecuredPresenter
{
    public function injectManager(UserManager $userManager): void
    {
        $this->manager = $userManager;
    }

    public function actionDefault($resourceId, $subResourceId): void
    {
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
        }

        $this->respondNotAllowed();
    }

    private function requestGetList(): void
    {
        $users = $this->manager->getList();

        $this->respondOk($this->arrayToJson($users));
    }
}
