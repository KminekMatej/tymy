<?php

namespace Tymy\Module\Event\Presenter\Api;

use Exception;
use Tymy\Module\Core\Presenter\Api\SecuredPresenter;
use Tymy\Module\Event\Manager\EventManager;
use Tymy\Module\Event\Model\Event;

/**
 * Description of DefaultPresenter
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 19. 9. 2020
 */
class DefaultPresenter extends SecuredPresenter
{
    public function injectManager(EventManager $manager): void
    {
        $this->manager = $manager;
    }

    public function actionDefault($resourceId, $subResourceId, ?string $filter, ?string $order, ?string $limit, ?string $offset)
    {
        if (empty($resourceId) && in_array($this->getRequest()->getMethod(), ["PUT", "DELETE"])) {
            $resourceId = $this->requestData["id"];
        }

        switch ($this->getRequest()->getMethod()) {
            case 'GET':
                $resourceId ? $this->requestGet($resourceId, $subResourceId) : $this->requestGetList($filter, $order, $limit, $offset);
                // no break
            case 'POST':
                $this->requestPost($resourceId);
                // no break
            case 'PUT':
                $this->needs($resourceId);
                $this->requestPut($resourceId, $subResourceId);
                // no break
            case 'DELETE':
                $this->needs($resourceId);
                $this->requestDelete($resourceId, $subResourceId);
        }

        $this->respondNotAllowed();
    }

    private function requestGetList(?string $filter, ?string $order, ?string $limit, ?string $offset)
    {
        $events = $this->manager->getListUserAllowed($this->user->getId(), $filter, $order, intval($limit), intval($offset));

        $this->respondOk($this->arrayToJson($events));
    }

    protected function requestPost($resourceId)
    {
        if ($this->isMultipleObjects($this->requestData)) {
            $events = [];
            foreach ($this->requestData as $data) {
                $events[] = $this->performPost($data);
            }
            $this->respondOkCreated($this->arrayToJson($events));
        } else {
            $this->respondOkCreated($this->performPost($this->requestData)->jsonSerialize());
        }
    }

    private function performPost(array $data): Event
    {
        try {
            $created = $this->manager->create($data);
        } catch (Exception $exc) {
            $this->handleException($exc);
        }

        return $created;
    }
}
