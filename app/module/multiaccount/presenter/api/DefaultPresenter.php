<?php

namespace App\Module\Multiaccount\Presenter\Api;

use App\Module\Core\Presenter\Api\SecuredPresenter;
use Tymy\Module\Multiaccount\Manager\MultiaccountManager;

/**
 * Description of DefaultPresenter
 * @RequestMapping(value = "/multiaccount/{team}", method = RequestMethod.GET)
 * @RequestMapping(value = "/multiaccount/{team}", method = RequestMethod.POST)
 * @RequestMapping(value = "/multiaccount/{team}", method = RequestMethod.DELETE)
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 06. 02. 2021
 */
class DefaultPresenter extends SecuredPresenter
{
    public function injectManager(MultiaccountManager $manager): void
    {
        $this->manager = $manager;
    }

    public function actionDefault($resourceId, $subResourceId)
    {
        switch ($this->getRequest()->getMethod()) {
            case 'GET':
                $resourceId ? $this->requestGet($resourceId, $subResourceId) : $this->requestGetList();
                // no break
            case 'POST':
                $this->needs($resourceId);
                $this->requestPost($resourceId);
                // no break
            case 'DELETE':
                $this->needs($resourceId);
                $this->requestDelete($resourceId, $subResourceId);
        }

        $this->respondNotAllowed();
    }

    private function requestGetList()
    {
        $teams = $this->manager->getListUserAllowed();

        $this->respondOk($this->arrayToJson($teams));
    }
}
