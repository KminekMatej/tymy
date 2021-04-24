<?php

namespace Tymy\Module\Right\Presenter;

use Tymy\Module\Core\Presenter\SecuredPresenter;
use Tymy\Module\Right\Manager\RightManager;

/**
 * Description of DefaultPresenter
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 28. 8. 2020
 */
class DefaultPresenter extends SecuredPresenter
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
        $users = $this->rightManager->getList();

        $this->respondOk($this->arrayToJson($users));
    }
}
