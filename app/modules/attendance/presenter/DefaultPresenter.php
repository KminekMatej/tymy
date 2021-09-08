<?php

namespace Tymy\Api\Module\Attendance\Presenters;

use Exception;
use Tymy\Module\Attendance\Manager\AttendanceManager;
use Tymy\Module\Attendance\Model\Attendance;
use Tymy\Api\Module\Core\Presenters\SecuredPresenter;

/**
 * Description of DefaultPresenter
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 13. 9. 2020
 */
class DefaultPresenter extends SecuredPresenter
{
    public function injectManager(AttendanceManager $manager): void
    {
        $this->manager = $manager;
    }

    public function actionDefault()
    {
        if ($this->getRequest()->getMethod() !== "POST") {
            $this->respondNotAllowed();
        }
        if (empty($this->requestData)) {
            $this->respondBadRequest();
        }

        if ($this->isMultipleObjects($this->requestData)) {
            $attendances = [];
            foreach ($this->requestData as $data) {
                $attendances[] = $this->performPost($data);
            }
            $this->respondOk($this->arrayToJson($attendances));
        } else {
            $this->respondOk($this->performPost($this->requestData)->jsonSerialize());
        }
    }

    private function performPost(array $data): Attendance
    {
        try {
            $created = $this->manager->create($data);
        } catch (Exception $exc) {
            $this->handleException($exc);
        }

        return $created;
    }
}
