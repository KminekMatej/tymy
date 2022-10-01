<?php

namespace Tymy\Module\User\Presenter\Api;

use Nette\InvalidArgumentException;
use Tymy\Module\Core\Presenter\Api\BasePresenter;
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
        $registeredUser = null;
        if ($this->getRequest()->getMethod() !== "POST") {
            $this->respondNotAllowed();
        }

        try {
            $registeredUser = $this->manager->register($this->requestData);
        } catch (InvalidArgumentException $exc) {
            $this->respondBadRequest($exc->getMessage());
        } catch (\Exception $exc) {
            $this->handleException($exc);
        }

        $this->respondOkCreated($registeredUser->jsonSerialize()); /* @phpstan-ignore-line */
    }
}
