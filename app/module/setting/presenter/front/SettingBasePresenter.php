<?php

namespace Tymy\Module\Setting\Presenter\Front;

use Tymy\Module\Attendance\Manager\StatusManager;
use Tymy\Module\Core\Presenter\Front\SecuredPresenter;
use Tymy\Module\Event\Manager\EventTypeManager;
use Tymy\Module\Permission\Manager\PermissionManager;
use Tymy\Module\Permission\Model\Permission;
use Tymy\Module\Permission\Model\Privilege;

/**
 * Description of SettingDefaultPresenter
 *
 * @author kminekmatej, 11. 9. 2021
 */
class SettingBasePresenter extends SecuredPresenter
{
    /** @inject */
    public PermissionManager $permissionManager;

    /** @inject */
    public EventTypeManager $eventTypeManager;

    /** @inject */
    public StatusManager $statusManager;
    protected array $eventTypes;
    protected array $userPermissions;

    protected function startup()
    {
        parent::startup();
        $this->eventTypes = $this->eventTypeManager->getIndexedList();
        $this->userPermissions = $this->permissionManager->getByType(Permission::TYPE_USER);
    }

    public function beforeRender()
    {
        parent::beforeRender();
        $this->template->eventTypes = $this->eventTypes;
        $this->template->statusList = $this->statusManager->getAllStatusCodes();
        $this->template->userPermissions = $this->userPermissions;
        $this->template->systemPermissions = $this->permissionManager->getByType(Permission::TYPE_SYSTEM);

        $this->addBreadcrumb($this->translator->translate("settings.setting", 2), $this->link(":Setting:Default:"));
    }

    /**
     * Test if currently logged user is allowed to operate under specified permission name
     * If user is not allowed to perform such thing, message is shown and gets redirected to Settings homepage
     *
     * @param string $permissionName Permission name
     * @param string $type Permission type, default SYS
     * @return void
     */
    protected function allowPermission(string $permissionName, string $type = "SYS"): void
    {
        if (!$this->getUser()->isAllowed($this->user->getId(), $type == "SYS" ? Privilege::SYS($permissionName) : Privilege::USR($permissionName))) {
            $this->flashMessage($this->translator->translate("common.alerts.notPermitted"));
            $this->redirect(":Setting:Default:");
        }
    }
}
