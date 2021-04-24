<?php

namespace Tymy\Module\Poll\Presenter;

use Tymy\Module\Core\Presenter\SecuredPresenter;
use Tymy\Module\Poll\Manager\PollManager;

/**
 * Description of MenuPresenter
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 27. 12. 2020
 */
class MenuPresenter extends SecuredPresenter
{
    public function injectManager(PollManager $manager): void
    {
        $this->manager = $manager;
    }

    public function actionDefault()
    {
        switch ($this->getRequest()->getMethod()) {
            case 'GET':
                $this->requestGetMenu();
        }

        $this->respondNotAllowed();
    }

    private function requestGetMenu()
    {
        $polls = $this->manager->getListMenu();

        $this->respondOk($this->arrayToJson($polls));
    }
}
