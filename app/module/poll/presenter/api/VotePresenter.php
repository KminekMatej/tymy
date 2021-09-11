<?php

namespace Tymy\Module\Poll\Presenter\Api;

use Tymy\Module\Core\Presenter\Api\SecuredPresenter;
use Tymy\Module\Poll\Manager\VoteManager;

/**
 * Description of VotePresenter
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 27. 12. 2020
 */
class VotePresenter extends SecuredPresenter
{
    public function injectManager(VoteManager $manager): void
    {
        $this->manager = $manager;
    }

    public function actionDefault($resourceId, $subResourceId)
    {
        switch ($this->getRequest()->getMethod()) {
            case 'POST':
                $this->requestPost($resourceId);
            // no break
        }

        $this->respondNotAllowed();
    }

}
