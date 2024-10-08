<?php

namespace Tymy\Module\Poll\Presenter\Api;

use Exception;
use Tymy\Module\Core\Presenter\Api\SecuredPresenter;
use Tymy\Module\Poll\Manager\OptionManager;
use Tymy\Module\Poll\Manager\PollManager;

/**
 * Description of OptionPresenter
 *
 * @RequestMapping(value = "/polls/{id}/options", method = RequestMethod.POST)
 * @RequestMapping(value = "/polls/{id}/options", method = RequestMethod.PUT)
 * @RequestMapping(value = "/polls/{id}/options", method = RequestMethod.DELETE)
 */
class OptionPresenter extends SecuredPresenter
{
    public function injectManager(OptionManager $manager): void
    {
        $this->manager = $manager;
    }

    public function actionDefault(int $resourceId, $subResourceId): void
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

    private function requestGetList(int $pollId): never
    {
        assert($this->manager instanceof OptionManager);
        $options = $this->manager->getPollOptions($pollId);

        $this->respondOk($this->arrayToJson($options));
    }

    protected function requestPost($resourceId): never
    {
        $created = null;
        if (!$this->isMultipleObjects($this->requestData)) {
            parent::requestPost($resourceId);   //if creating just one poll, use parent post handler
        }

        //if creating multiple options (standard use), call special function
        try {
            assert($this->manager instanceof OptionManager);
            $created = $this->manager->createMultiple($this->requestData, $resourceId);
        } catch (Exception $exc) {
            $this->respondByException($exc);
        }

        $this->respondOkCreated($this->arrayToJson($created));
    }

    /**
     * Parse out option id from request data
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
