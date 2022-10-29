<?php

namespace Tymy\Module\Right\Presenter\Api;

use Tymy\Module\Core\Presenter\Api\SecuredPresenter;
use Tymy\Module\Right\Manager\RightManager;

/**
 * Description of UserPresenter
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 28. 8. 2020
 */
class UserPresenter extends SecuredPresenter
{
    /** @var RightManager @inject */
    public RightManager $rightManager;

    public function actionDefault(): void
    {
        if ($this->getRequest()->getMethod() === 'GET') {
            $this->requestGetList();
        }

        $this->respondNotAllowed();
    }

    private function requestGetList(): void
    {
        $records = $this->rightManager->getListAllowed($this->user->getId());

        $this->respondOk($this->arrayToJson($records));
    }
}
