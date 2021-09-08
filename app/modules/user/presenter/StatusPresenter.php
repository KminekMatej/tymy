<?php

namespace Tymy\Api\Module\User\Presenters;

use Tymy\Api\Module\Core\Presenters\SecuredPresenter;
use Tymy\Module\User\Manager\UserManager;

/**
 * Description of StatusPresenter
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 1. 9. 2020
 */
class StatusPresenter extends SecuredPresenter
{
    public function injectManager(UserManager $userManager)
    {
        $this->manager = $userManager;
    }

    public function actionDefault($status)
    {
        if ($this->getRequest()->getMethod() !== "GET") {
            $this->respondNotAllowed();
        }

        if (empty($status) || !in_array($status, ["PLAYER", "MEMBER", "INIT", "SICK"])) {
            $this->respondNotFound();
        }

        $this->respondOk($this->arrayToJson($this->manager->getByStatus($status)));
    }
}
