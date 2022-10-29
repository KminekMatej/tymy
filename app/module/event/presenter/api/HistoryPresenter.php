<?php

namespace Tymy\Module\Event\Presenter\Api;

use Exception;
use Tymy\Module\Attendance\Manager\HistoryManager;
use Tymy\Module\Core\Presenter\Api\SecuredPresenter;

/**
 * Description of HistoryPresenter
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 08.10. 2020
 */
class HistoryPresenter extends SecuredPresenter
{
    public function injectManager(HistoryManager $manager): void
    {
        $this->manager = $manager;
    }

    public function actionDefault(mixed $resourceId, $subResourceId): void
    {
        if ($this->getRequest()->getMethod() != "GET") {
            $this->respondNotAllowed();
        }

        $this->needs($resourceId);
        $this->requestGetList($resourceId);
    }

    protected function requestGetList($eventId): void
    {
        $records = null;
        try {
            $records = $this->manager->readForEvent($eventId);
        } catch (Exception $exc) {
            $this->handleException($exc);
        }

        $this->respondOk($this->arrayToJson($records));
    }
}
