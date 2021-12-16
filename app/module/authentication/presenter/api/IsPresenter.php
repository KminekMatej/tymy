<?php

namespace Tymy\Module\Authentication\Presenter\Api;

use Tymy\Module\Core\Presenter\Api\BasePresenter;
use Tymy\Module\Team\Manager\TeamManager;

/**
 * Description of IsPresenter
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 6. 8. 2020
 */
class IsPresenter extends BasePresenter
{
    /** @inject */
    public TeamManager $teamManager;

    public function actionDefault()
    {
        if ($this->getRequest()->getMethod() !== "GET") {
            $this->respondNotAllowed();
        }

        $this->responder->A200_OK($this->teamManager->getTeamSimple()->jsonSerialize());
    }
}
