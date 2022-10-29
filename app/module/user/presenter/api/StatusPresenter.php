<?php

namespace Tymy\Module\User\Presenter\Api;

use Tymy\Module\Core\Presenter\Api\SecuredPresenter;
use Tymy\Module\User\Manager\UserManager;

/**
 * Description of StatusPresenter
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 1. 9. 2020
 */
class StatusPresenter extends SecuredPresenter
{
    public function injectManager(UserManager $userManager): void
    {
        $this->manager = $userManager;
    }

    public function actionDefault($status): void
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
