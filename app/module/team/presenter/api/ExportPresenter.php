<?php

namespace Tymy\Module\Team\Presenter\Api;

use Tymy\Module\Core\Presenter\Api\SecuredPresenter;
use Tymy\Module\User\Model\User;
use Vojir\Responses\CsvResponse\ComposedCsvResponse;

/**
 * Description of ExportPresenter
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 18.10.2022
 */
class ExportPresenter extends SecuredPresenter
{
    public function actionDefault(?string $status = null): void
    {
        if ($this->getRequest()->getMethod() != 'GET') {
            $this->respondNotAllowed();
        }

        $users = isset($status) ? $this->userManager->getByStatus($status) : $this->userManager->getList();

        if (empty($users)) {
            $this->respondNotFound();
        }

        $csvData = array_map(fn($entity) => /* @var $entity User */
            $entity->csvSerialize(), $users);

        $response = new ComposedCsvResponse($csvData, 'users' . ($status ? "-$status" : "") . '.csv', true);
        $response->setGlue(ComposedCsvResponse::COMMA);
        $this->sendResponse($response);
    }

    private function requestGetList(): void
    {
        $users = $this->rightManager->getList();

        $this->respondOk($this->arrayToJson($users));
    }
}
