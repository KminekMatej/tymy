<?php

namespace Tymy\Module\Authentication\Presenter;

use Tymy\Module\Core\Presenter\BasePresenter;
use Tymy\Module\Team\Manager\TeamManager;

/**
 * Description of IsPresenter
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 6. 8. 2020
 */
class IsPresenter extends BasePresenter
{
    /** @var TeamManager @inject */
    public $teamManager;

    public function actionDefault()
    {
        $this->teamManager->isFeatureAllowed("api") === true ? $this->is() : $this->isNot();
    }

    private function is()
    {
        if ($this->getRequest()->getMethod() !== "GET") {
            $this->respondNotAllowed();
        }

        $this->responder->A200_OK($this->teamManager->getTeamSimple()->jsonSerialize());
    }

    private function isNot()
    {
        $this->responder->E403_FORBIDDEN("API disabled");
    }
}
