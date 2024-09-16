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
    #[\Nette\DI\Attributes\Inject]
    public AuthorizationManager $AuthorizationManager;

    #[\Nette\DI\Attributes\Inject]
    public UserManager $userManager;

    public function actionDefault($resourceId): void
    {
        if (empty($resourceId)) {
            $this->respondBadRequest();
        }

        if ($this->getRequest()->getMethod() !== "GET") {
            $this->respondForbidden();
        }

        $this->requestGetList($resourceId);
    }

    private function requestGetList(int $userId): never
    {
        $rights = null;
        try {
            $rights = $this->AuthorizationManager->getListUserAllowed($this->userManager->getById($userId));
        } catch (\Exception $exc) {
            $this->respondByException($exc);
        }

        $this->respondOk($rights);
    }
}
