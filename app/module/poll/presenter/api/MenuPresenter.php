<?php

namespace Tymy\Module\Poll\Presenter\Api;

use Tymy\Module\Core\Presenter\Api\SecuredPresenter;
use Tymy\Module\Poll\Manager\PollManager;

/**
 * Description of MenuPresenter
 */
class MenuPresenter extends SecuredPresenter
{
    public function injectManager(PollManager $manager): void
    {
        $this->manager = $manager;
    }

    public function actionDefault(): void
    {
        if ($this->getRequest()->getMethod() === 'GET') {
            $this->requestGetMenu();
        }

        $this->respondNotAllowed();
    }

    private function requestGetMenu(): void
    {
        assert($this->manager instanceof PollManager);
        $polls = $this->manager->getListUserAllowed();

        $this->respondOk($this->arrayToJson($polls));
    }
}
