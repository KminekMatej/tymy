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

    public function actionStatus($resourceId, $subResourceId): void
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

    public function actionStatusSet($resourceId, $subResourceId): void
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

    private function requestStatusGet(int $resourceId, ?int $subResourceId): void
    {
        $record = null;
        try {
            $record = $this->statusSetManager->read($resourceId, $subResourceId);
        } catch (Exception $exc) {
            $this->handleException($exc);
        }

        $this->respondOk($record->jsonSerialize()); /* @phpstan-ignore-line */
    }

    private function requestStatusGetList(): void
    {
        $statuses = null;
        try {
            $statuses = $this->statusSetManager->getListUserAllowed($this->user->getId());
        } catch (Exception $exc) {
            $this->handleException($exc);
        }

        $this->respondOk($this->arrayToJson($statuses)); /* @phpstan-ignore-line */
    }

    private function requestStatusPost(?int $resourceId): void
    {
        $created = null;
        try {
            $created = $this->statusManager->create($this->requestData, $resourceId);
        } catch (Exception $exc) {
            $this->handleException($exc);
        }

        $this->respondOkCreated($created->jsonSerialize()); /* @phpstan-ignore-line */
    }

    private function requestStatusPut(int $resourceId, ?int $subResourceId): void
    {
        $updated = null;
        try {
            $updated = $this->statusManager->update($this->requestData, $resourceId, $subResourceId);
        } catch (Exception $exc) {
            $this->handleException($exc);
        }

        $this->respondOk($updated->jsonSerialize()); /* @phpstan-ignore-line */
    }

    private function requestStatusDelete(int $resourceId, ?int $subResourceId): void
    {
        $deletedId = null;
        try {
            $deletedId = $this->statusManager->delete($resourceId, $subResourceId);
        } catch (Exception $exc) {
            $this->handleException($exc);
        }

        $this->respondDeleted($deletedId); /* @phpstan-ignore-line */
    }

    private function requestStatusSetPost(?int $resourceId): void
    {
        $created = null;
        try {
            $created = $this->statusSetManager->create($this->requestData, $resourceId);
        } catch (Exception $exc) {
            $this->handleException($exc);
        }

        $this->respondOkCreated($created->jsonSerialize()); /* @phpstan-ignore-line */
    }

    private function requestStatusSetPut(int $resourceId, ?int $subResourceId): void
    {
        $updated = null;
        try {
            $updated = $this->statusSetManager->update($this->requestData, $resourceId, $subResourceId);
        } catch (Exception $exc) {
            $this->handleException($exc);
        }

        $this->respondOk($updated->jsonSerialize()); /* @phpstan-ignore-line */
    }

    private function requestStatusSetDelete(int $resourceId, ?int $subResourceId): void
    {
        $deletedId = null;
        try {
            $deletedId = $this->statusSetManager->delete($resourceId, $subResourceId);
        } catch (Exception $exc) {
            $this->handleException($exc);
        }

        $this->respondDeleted($deletedId); /* @phpstan-ignore-line */
    }
}
