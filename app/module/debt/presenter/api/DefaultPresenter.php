<?php

namespace Tymy\Module\Debt\Presenter\Api;

use Tymy\Module\Core\Presenter\Api\SecuredPresenter;
use Tymy\Module\Debt\Manager\DebtManager;

/**
 * Description of DefaultPresenter
 */
class DefaultPresenter extends SecuredPresenter
{
    public function injectManager(DebtManager $manager): void
    {
        $this->manager = $manager;
    }

    public function actionDefault($resourceId, $subResourceId): void
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

    private function requestGetList(): void
    {
        assert($this->manager instanceof DebtManager);

        $debts = $this->manager->getListUserAllowed();

        $this->respondOk($this->arrayToJson($debts));
    }
}
