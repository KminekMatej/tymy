<?php

namespace Tymy\Api\Module\User\Presenters;

use Exception;
use Tymy\Api\Module\Core\Presenters\BasePresenter;
use Tymy\Module\User\Manager\UserManager;

/**
 * Description of RegisterPresenter
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 1. 9. 2020
 */
class RegisterPresenter extends BasePresenter
{
    public function injectManager(UserManager $userManager)
    {
        $this->manager = $userManager;
    }

    public function actionDefault()
    {
        if ($this->getRequest()->getMethod() !== "POST") {
            $this->respondNotAllowed();
        }

        try {
            $registeredUser = $this->manager->register($this->requestData);
        } catch (Exception $exc) {
            $this->handleException($exc);
        }

        $this->respondOkCreated($registeredUser->jsonSerialize());
    }
}
