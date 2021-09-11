<?php

namespace Tymy\Module\Core\Presenter\Api;

use Tymy\Module\User\Model\User;

/**
 * Description of SecuredPresenter
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 2. 8. 2020
 */
class SecuredPresenter extends BasePresenter
{
    public function startup()
    {
        parent::startup();

        if ($tsid = $this->getParameter("TSID")) {
            if (session_id()) {
                session_abort();
            }
            session_id($tsid);
            session_start();
        }

        if (!isset($this->user) || !$this->user->isLoggedIn()) {
            $this->respondUnauthorized();
        }
    }

    /**
     * Function responds immediately 403:FORBIDDEN for non-admin users
     */
    protected function allowAdmin()
    {
        if (!$this->user->isInRole(User::ROLE_SUPER)) {
            $this->respondForbidden();
        }
    }
}
