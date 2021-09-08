<?php

namespace App\Module\Poll\Presenter\Api;

use App\Module\Core\Presenter\Api\SecuredPresenter;
use Tymy\Module\Poll\Manager\OptionManager;

/**
 * Description of OptionPresenter
 *
 *
 * @RequestMapping(value = "/polls/{id}/options", method = RequestMethod.POST)
 * @RequestMapping(value = "/polls/{id}/options", method = RequestMethod.PUT)
 * @RequestMapping(value = "/polls/{id}/options", method = RequestMethod.DELETE)
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 27. 12. 2020
 */
class OptionPresenter extends SecuredPresenter
{
    public function injectManager(OptionManager $manager): void
    {
        $this->manager = $manager;
    }

    public function actionDefault($resourceId, $subResourceId)
    {
        switch ($this->getRequest()->getMethod()) {
            case 'GET':
                $this->needs($resourceId);
                $this->requestGetList($resourceId);
                // no break
            case 'POST':
                $this->needs($resourceId);
                $this->requestPost($resourceId);
                // no break
            case 'PUT':
                $this->needs($resourceId);
                $this->requestPut($resourceId, $this->getOptionIdFromData());
                // no break
            case 'DELETE':
                $this->needs($resourceId);
                $this->requestDelete($resourceId, $this->getOptionIdFromData());
        }

        $this->respondNotAllowed();
    }

    private function requestGetList(int $pollId)
    {
        $options = $this->manager->getPollOptions($pollId);

        $this->respondOk($this->arrayToJson($options));
    }

    protected function requestPost($resourceId)
    {
        if (!$this->isMultipleObjects($this->requestData)) {
            parent::requestPost($resourceId);   //if creating just one poll, use parent post handler
        }

        //if creating multiple options (standard use), call special function
        try {
            $created = $this->manager->createMultiple($this->requestData, $resourceId);
        } catch (Exception $exc) {
            $this->handleException($exc);
        }

        $this->respondOkCreated($this->arrayToJson($created));
    }

    /**
     * Parse out option id from request data
     *
     * @return int
     */
    private function getOptionIdFromData(): int
    {
        if (empty($this->requestData)) {
            $this->respondBadRequest();
        }

        if (!isset($this->requestData["id"])) {
            $this->responder->E4013_MISSING_INPUT("id");
        }

        if (!is_int($this->requestData["id"])) {
            $this->responder->E4014_EMPTY_INPUT("id");
        }

        return $this->requestData["id"];
    }
}
