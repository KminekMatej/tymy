<?php

namespace Tymy\Module\Authentication\Presenter\Api;

use Nette\Security\AuthenticationException;
use Tymy\Module\Core\Manager\BaseManager;
use Tymy\Module\Core\Presenter\Api\BasePresenter;
use Tymy\Module\Multiaccount\Manager\MultiaccountManager;
use Tymy\Module\User\Manager\UserManager;

/**
 * Description of DefaultPresenter
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 2. 8. 2020
 */
class DefaultPresenter extends BasePresenter
{
    /** @inject */
    public UserManager $userManager;

    /** @inject */
    public MultiaccountManager $maManager;

    public function actionIn($username, $password)
    {
        try {
            $this->user->login($this->requestData["login"] ?? $username, $this->requestData["password"] ?? $password);
            $this->user->setExpiration('+ 14 days');
            BaseManager::logg($this->team, ($this->requestData["login"] ?? $username) . " API login");
        } catch (AuthenticationException $e) {
            $this->responder->E401_UNAUTHORIZED("Not logged in");
        }

        $userId = $this->user->getIdentity()->getData()["id"];
        $this->responder->A2001_LOGGED_IN($this->userManager->getById($userId)->jsonSerialize(), session_id());
    }

    public function actionInTk(string $tk)
    {
        if (empty($tk)) {
            $this->respondBadRequest();
        }

        try {
            $this->user->login("tk|$tk");
            $this->user->setExpiration('+ 14 days');
        } catch (AuthenticationException $e) {
            $this->responder->E401_UNAUTHORIZED("Not logged in");
        }

        $userId = $this->user->getIdentity()->getData()["id"];
        $this->responder->A2001_LOGGED_IN($this->userManager->getById($userId)->jsonSerialize(), session_id());
    }

    public function actionOut()
    {
        $this->user->logout();
        $this->respondOk();
    }
}
