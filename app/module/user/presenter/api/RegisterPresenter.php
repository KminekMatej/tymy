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
    public function injectManager(UserManager $userManager): void
    {
        $this->manager = $userManager;
    }

    public function actionDefault(): void
    {
        $registeredUser = null;
        if ($this->getRequest()->getMethod() !== "POST") {
            $this->respondNotAllowed();
        }

        try {
            assert($this->manager instanceof UserManager);
            $registeredUser = $this->manager->register($this->requestData);
        } catch (InvalidArgumentException $exc) {
            $this->respondBadRequest($exc->getMessage());
        } catch (\Exception $exc) {
            $this->respondByException($exc);
        }

        $this->respondOkCreated($registeredUser->jsonSerialize());
    }
}
