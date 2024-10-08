<?php

namespace Tymy\Module\Poll\Presenter\Api;

use Tymy\Module\Core\Presenter\Api\SecuredPresenter;
use Tymy\Module\Poll\Manager\VoteManager;

/**
 * Description of VotePresenter
 */
class VotePresenter extends SecuredPresenter
{
    public function injectManager(VoteManager $manager): void
    {
        $this->manager = $manager;
    }

    public function actionDefault($resourceId, $subResourceId): void
    {
        if ($this->getRequest()->getMethod() === 'POST') {
            $this->requestPost($resourceId);
        }

        $this->respondNotAllowed();
    }
}
