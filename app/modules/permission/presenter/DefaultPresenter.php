<?php

namespace Tymy\Module\Permission\Presenter;

use Tymy\Module\Core\Presenter\SecuredPresenter;
use Tymy\Module\Permission\Manager\PermissionManager;

/**
 * Description of DefaultPresenter
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 28. 8. 2020
 */
class DefaultPresenter extends SecuredPresenter
{
    /** @var PermissionManager @inject */
    public PermissionManager $permissionManager;

    public function injectManager(PermissionManager $manager): void
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
                $this->requestPost($resourceId);
                // no break
            case 'PUT':
                $this->requestPut($resourceId, $subResourceId);
                // no break
            case 'DELETE':
                $this->requestDelete($resourceId, $subResourceId);
        }

        $this->respondNotAllowed();
    }

    public function actionName(string $name)
    {
        $this->allowAdmin();

        $permission = $this->manager->getByName($name);

        if (!$permission) {
            $this->respondNotFound();
        }

        if ($this->getRequest()->getMethod() == "GET") {
            $this->respondOk($permission->jsonSerialize());
        } else {
            $this->respondNotAllowed();
        }
    }

    public function actionType(string $name)
    {
        $this->allowAdmin();

        $permissions = $this->manager->getByType($name);

        if ($this->getRequest()->getMethod() == "GET") {
            $this->respondOk($this->arrayToJson($permissions));
        } else {
            $this->respondNotAllowed();
        }
    }

    private function requestGetList()
    {
        $this->allowAdmin();

        $permissions = $this->manager->getList();

        $this->respondOk($this->arrayToJson($permissions));
    }
}
