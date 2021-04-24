<?php

namespace Tymy\Module\Right\Presenter;

use Tymy\Module\Core\Presenter\SecuredPresenter;
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

    public function actionDefault()
    {
        switch ($this->getRequest()->getMethod()) {
            case 'GET':
                $this->requestGetList();
        }

        $this->respondNotAllowed();
    }

    private function requestGetList()
    {
        $records = $this->rightManager->getListAllowed($this->user->getId());

        $this->respondOk($this->arrayToJson($records));
    }
}
