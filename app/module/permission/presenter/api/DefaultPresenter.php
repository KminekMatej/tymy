<?php

namespace Tymy\Module\Permission\Presenter\Api;

use Tymy\Module\Core\Presenter\Api\SecuredPresenter;
use Tymy\Module\Permission\Manager\PermissionManager;

/**
 * Description of DefaultPresenter
 */
class DefaultPresenter extends SecuredPresenter
{
    /** @var PermissionManager @inject */
    public PermissionManager $permissionManager;

    public function injectManager(PermissionManager $manager): void
    {
        $this->manager = $manager;
    }

    public function actionDefault($resourceId, $subResourceId): void
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

    public function actionName(string $name): void
    {
        $this->allowAdmin();

        $permission = $this->permissionManager->getByName($name);

        if (!$permission) {
            $this->respondNotFound();
        }

        if ($this->getRequest()->getMethod() == "GET") {
            $this->respondOk($permission->jsonSerialize());
        } else {
            $this->respondNotAllowed();
        }
    }

    public function actionType(string $name): void
    {
        $this->allowAdmin();

        $permissions = $this->permissionManager->getByType($name);

        if ($this->getRequest()->getMethod() == "GET") {
            $this->respondOk($this->arrayToJson($permissions));
        } else {
            $this->respondNotAllowed();
        }
    }

    private function requestGetList(): void
    {
        $this->allowAdmin();

        $permissions = $this->manager->getList();

        $this->respondOk($this->arrayToJson($permissions));
    }
}
