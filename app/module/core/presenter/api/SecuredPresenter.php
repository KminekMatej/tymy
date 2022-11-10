<?php

namespace Tymy\Module\Core\Presenter\Api;

use Nette\Security\AuthenticationException;
use Tracy\Debugger;
use Tymy\Module\Core\Manager\BaseManager;
use Tymy\Module\User\Model\User;

/**
 * Description of SecuredPresenter
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 2. 8. 2020
 */
class SecuredPresenter extends BasePresenter
{
    private const TSID = "TSID";
    private const LOGIN_PARAM = "login";
    private const PASSWORD_PARAM = "password";

    public function startup(): void
    {
        parent::startup();

        if ($tsid = $this->getParameter(self::TSID) ?: $this->httpRequest->getHeader(self::TSID)) {
            if ($this->session->getId() !== '' && $this->session->getId() !== '0') {
                $this->session->close();
            }
            session_id($tsid);
            $this->session->start();
            $this->user->refreshStorage();
        }

        if (!$this->user->isLoggedIn()) {
            $username = $this->getParameter(self::LOGIN_PARAM) ?: $this->httpRequest->getHeader(self::LOGIN_PARAM);
            $password = $this->getParameter(self::PASSWORD_PARAM) ?: $this->httpRequest->getHeader(self::PASSWORD_PARAM);

            if (!empty($username) && !empty($password)) {
                try {
                    $this->user->setExpiration(null);
                    $this->user->login($username, $password);
                    BaseManager::logg($this->team, "$username API direct access");
                } catch (AuthenticationException $exc) {
                    $this->responder->E401_UNAUTHORIZED("Not logged in");
                }
            }

            if ($this->user->isLoggedIn()) {
                $this->initUser();
                Debugger::log($this->tymyUser->getCallName() . "@" . $this->team->getSysName() . " directly accessed");
            }
        }

        if ($this->user === null || !$this->user->isLoggedIn()) {
            $this->respondUnauthorized();
        }
    }

    /**
     * Function responds immediately 403:FORBIDDEN for non-admin users
     */
    protected function allowAdmin(): void
    {
        if (!$this->user->isInRole(User::ROLE_SUPER)) {
            $this->respondForbidden();
        }
    }
}
