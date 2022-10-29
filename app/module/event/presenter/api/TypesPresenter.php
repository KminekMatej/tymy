<?php

namespace Tymy\Module\Event\Presenter\Api;

use Tymy\Module\Core\Presenter\Api\SecuredPresenter;
use Tymy\Module\Event\Manager\EventTypeManager;

/**
 * Description of TypesPresenter
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 08.10. 2020
 */
class TypesPresenter extends SecuredPresenter
{
    public function injectManager(EventTypeManager $manager): void
    {
        $this->manager = $manager;
    }

    public function actionDefault($resourceId, $subResourceId): void
    {
        if ($this->getRequest()->getMethod() != "GET") {
            $this->respondNotAllowed();
        }

        $this->requestGetList();
    }

    private function requestGetList(): void
    {
        $events = $this->manager->getListUserAllowed($this->user->getId());

        $this->respondOk($this->arrayToJson($events));
    }
}
