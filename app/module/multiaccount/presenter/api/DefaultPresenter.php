<?php

namespace Tymy\Module\Multiaccount\Presenter\Api;

use Exception;
use Tymy\Module\Core\Presenter\Api\SecuredPresenter;
use Tymy\Module\Multiaccount\Manager\MultiaccountManager;
use Tymy\Module\Multiaccount\Model\TransferKey;

/**
 * Description of DefaultPresenter
 * @RequestMapping(value = "/multiaccount", method = RequestMethod.GET)
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

    public function actionDefault(?string $resourceId): void
    {
        switch ($this->getRequest()->getMethod()) {
            case 'GET':
                $resourceId ? $this->requestGenerateKey($resourceId) : $this->requestGetList();
                // no break
            case 'POST':
                $this->needs($resourceId);
                $this->requestAddTeam($resourceId);
                // no break
            case 'DELETE':
                $this->needs($resourceId);
                $this->requestRemoveTeam($resourceId);
        }

        $this->respondNotAllowed();
    }

    private function requestGenerateKey(string $team): void
    {
        assert($this->manager instanceof MultiaccountManager);
        $tk = $this->manager->generateNewTk($team);
        assert($tk instanceof TransferKey);

        $this->respondOk([
            "transferKey" => $tk->getTransferKey(),
            "uid" => $tk->getUid(),
        ]);
    }

    private function requestAddTeam(string $team)
    {
        try {
            $this->manager->create($this->requestData, $team);
        } catch (Exception $exc) {
            $this->handleException($exc);
        }

        $this->respondOkCreated();
    }

    private function requestRemoveTeam(string $team)
    {
        try {
            assert($this->manager instanceof MultiaccountManager);
            $this->manager->delete($team);
        } catch (Exception $exc) {
            $this->handleException($exc);
        }

        $this->respondOk();
    }

    private function requestGetList(): void
    {
        assert($this->manager instanceof MultiaccountManager);
        $teams = $this->manager->getListUserAllowed();

        $this->respondOk($this->arrayToJson($teams));
    }
}
