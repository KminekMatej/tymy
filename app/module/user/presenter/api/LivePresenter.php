<?php

namespace Tymy\Module\User\Presenter\Api;

use Tymy\Module\Core\Presenter\Api\SecuredPresenter;
use Tymy\Module\User\Manager\UserManager;

/**
 * Description of LivePresenter
 */
class LivePresenter extends SecuredPresenter
{
    public function injectManager(UserManager $userManager): void
    {
        $this->manager = $userManager;
    }

    public function actionDefault($resourceId, $subResourceId): void
    {
        if ($this->getRequest()->getMethod() !== "GET") {
            $this->respondNotAllowed();
        }

        assert($this->manager instanceof UserManager);
        $liveUsers = $this->manager->getLiveUsers();

        $this->respondOk($liveUsers);
    }
}
