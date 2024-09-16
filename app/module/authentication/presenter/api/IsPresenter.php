<?php

namespace Tymy\Module\Authentication\Presenter\Api;

use Tymy\Module\Core\Presenter\Api\BasePresenter;
use Tymy\Module\Team\Manager\TeamManager;

/**
 * Description of IsPresenter
 */
class IsPresenter extends BasePresenter
{
    #[\Nette\DI\Attributes\Inject]
    public TeamManager $teamManager;

    public function actionDefault(): void
    {
        if ($this->getRequest()->getMethod() !== "GET") {
            $this->respondNotAllowed();
        }

        $this->responder->A200_OK($this->teamManager->getTeamSimple()->jsonSerialize());
    }
}
