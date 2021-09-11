<?php

namespace Tymy\Module\User\Presenter\Api;

use Tymy\Module\Core\Presenter\Api\SecuredPresenter;
use Tymy\Module\User\Manager\UserManager;

/**
 * Description of LivePresenter
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 14. 2. 2021
 */
class LivePresenter extends SecuredPresenter
{
    public function injectManager(UserManager $userManager)
    {
        $this->manager = $userManager;
    }

    public function actionDefault($resourceId, $subResourceId)
    {
        if ($this->getRequest()->getMethod() !== "GET") {
            $this->respondNotAllowed();
        }

        $liveUsers = $this->manager->getLiveUsers();

        $this->respondOk($liveUsers);
    }
}
