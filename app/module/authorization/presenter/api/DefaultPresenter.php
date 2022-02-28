<?php

namespace Tymy\Module\Authorization\Presenter\Api;

use Swoole\MySQL\Exception;
use Tymy\Module\Authorization\Manager\AuthorizationManager;
use Tymy\Module\Core\Presenter\Api\BasePresenter;
use Tymy\Module\User\Manager\UserManager;

/**
 * Description of DefaultPresenter
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 22. 02. 2021
 */
class DefaultPresenter extends BasePresenter
{
    /** @inject */
    public AuthorizationManager $AuthorizationManager;

    /** @inject */
    public UserManager $userManager;

    public function actionDefault($resourceId)
    {
        if (empty($resourceId)) {
            $this->respondBadRequest();
        }

        if ($this->getRequest()->getMethod() !== "GET") {
            $this->respondForbidden();
        }

        $this->requestGetList($resourceId);
    }

    private function requestGetList(int $userId)
    {
        try {
            $rights = $this->AuthorizationManager->getListUserAllowed($this->userManager->getById($userId));
        } catch (\Exception $exc) {
            $this->handleException($exc);
        }

        $this->respondOk($rights); /* @phpstan-ignore-line */
    }
}
