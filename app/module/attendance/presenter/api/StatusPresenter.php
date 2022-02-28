<?php

namespace Tymy\Module\Attendance\Presenter\Api;

use Exception;
use Tymy\Module\Attendance\Manager\StatusManager;
use Tymy\Module\Attendance\Manager\StatusSetManager;
use Tymy\Module\Core\Presenter\Api\SecuredPresenter;

/**
 * Description of StatusPresenter
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 3. 11. 2020
 */
class StatusPresenter extends SecuredPresenter
{
    /** @inject */
    public StatusManager $statusManager;

    /** @inject */
    public StatusSetManager $statusSetManager;

    public function actionStatus($resourceId, $subResourceId)
    {
        switch ($this->getRequest()->getMethod()) {
            case "GET":
                $resourceId ? $this->requestStatusGet($resourceId, $subResourceId) : $this->requestStatusGetList();
                // no break
            case "POST":
                $this->requestStatusPost($resourceId);
                // no break
            case "PUT":
                $this->requestStatusPut($resourceId, $subResourceId);
                // no break
            case "DELETE":
                $this->requestStatusDelete($resourceId, $subResourceId);
        }

        $this->respondNotAllowed();
    }

    public function actionStatusSet($resourceId, $subResourceId)
    {
        switch ($this->getRequest()->getMethod()) {
            case "POST":
                $this->requestStatusSetPost($resourceId);
                // no break
            case "PUT":
                $this->requestStatusSetPut($resourceId, $subResourceId);
                // no break
            case "DELETE":
                $this->requestStatusSetDelete($resourceId, $subResourceId);
        }

        $this->respondNotAllowed();
    }

    private function requestStatusGet($resourceId, $subResourceId)
    {
        try {
            $record = $this->statusSetManager->read($resourceId, $subResourceId);
        } catch (Exception $exc) {
            $this->handleException($exc);
        }

        $this->respondOk($record->jsonSerialize()); /* @phpstan-ignore-line */
    }

    private function requestStatusGetList()
    {
        try {
            $statuses = $this->statusSetManager->getListUserAllowed($this->user->getId());
        } catch (Exception $exc) {
            $this->handleException($exc);
        }

        $this->respondOk($this->arrayToJson($statuses)); /* @phpstan-ignore-line */
    }

    private function requestStatusPost($resourceId)
    {
        try {
            $created = $this->statusManager->create($this->requestData, $resourceId);
        } catch (Exception $exc) {
            $this->handleException($exc);
        }

        $this->respondOkCreated($created->jsonSerialize()); /* @phpstan-ignore-line */
    }

    private function requestStatusPut($resourceId, $subResourceId)
    {
        try {
            $updated = $this->statusManager->update($this->requestData, $resourceId, $subResourceId);
        } catch (Exception $exc) {
            $this->handleException($exc);
        }

        $this->respondOk($updated->jsonSerialize()); /* @phpstan-ignore-line */
    }

    private function requestStatusDelete($resourceId, $subResourceId)
    {
        try {
            $deletedId = $this->statusManager->delete($resourceId, $subResourceId);
        } catch (Exception $exc) {
            $this->handleException($exc);
        }

        $this->respondDeleted($deletedId); /* @phpstan-ignore-line */
    }

    private function requestStatusSetPost($resourceId)
    {
        try {
            $created = $this->statusSetManager->create($this->requestData, $resourceId);
        } catch (Exception $exc) {
            $this->handleException($exc);
        }

        $this->respondOkCreated($created->jsonSerialize()); /* @phpstan-ignore-line */
    }

    private function requestStatusSetPut($resourceId, $subResourceId)
    {
        try {
            $updated = $this->statusSetManager->update($this->requestData, $resourceId, $subResourceId);
        } catch (Exception $exc) {
            $this->handleException($exc);
        }

        $this->respondOk($updated->jsonSerialize()); /* @phpstan-ignore-line */
    }

    private function requestStatusSetDelete($resourceId, $subResourceId)
    {
        try {
            $deletedId = $this->statusSetManager->delete($resourceId, $subResourceId);
        } catch (Exception $exc) {
            $this->handleException($exc);
        }

        $this->respondDeleted($deletedId); /* @phpstan-ignore-line */
    }
}
