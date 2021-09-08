<?php

namespace App\Module\Debt\Presenter\Api;

use App\Module\Core\Presenter\Api\SecuredPresenter;
use Tymy\Module\Debt\Manager\DebtManager;

/**
 * Description of DefaultPresenter
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 30. 11. 2020
 */
class DefaultPresenter extends SecuredPresenter
{
    public function injectManager(DebtManager $manager): void
    {
        $this->manager = $manager;
    }

    public function actionDefault($resourceId, $subResourceId)
    {
        switch ($this->getRequest()->getMethod()) {
            case 'GET':
                $resourceId ? $this->requestGet($resourceId, $subResourceId) : $this->requestGetList();
                // no break
            case 'POST':
                $this->requestPost($resourceId);
                // no break
            case 'PUT':
                $this->needs($resourceId);
                $this->requestPut($resourceId, $subResourceId);
                // no break
            case 'DELETE':
                $this->needs($resourceId);
                $this->requestDelete($resourceId, $subResourceId);
        }

        $this->respondNotAllowed();
    }

    private function requestGetList()
    {
        $debts = $this->manager->getListUserAllowed($this->user->getId());

        $this->respondOk($this->arrayToJson($debts));
    }
}
