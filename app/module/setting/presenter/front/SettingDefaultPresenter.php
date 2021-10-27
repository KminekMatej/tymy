<?php

namespace Tymy\Module\Setting\Presenter\Front;

use Tymy\Module\Attendance\Manager\StatusManager;
use Tymy\Module\Core\Presenter\Front\SecuredPresenter;
use Tymy\Module\Event\Manager\EventTypeManager;
use Tymy\Module\Permission\Manager\PermissionManager;
use Tymy\Module\Permission\Model\Permission;

/**
 * Description of SettingDefaultPresenter
 *
 * @author kminekmatej, 11. 9. 2021
 */
class SettingDefaultPresenter extends SecuredPresenter
{

    /** @inject */
    private PermissionManager $permissionManager;

    /** @inject */
    private EventTypeManager $eventTypeManager;

    /** @inject */
    private StatusManager $statusManager;

    public function beforeRender()
    {
        parent::beforeRender();
        $this->template->eventTypes = $this->eventTypeManager->getList();
        $this->template->statusList = $this->statusManager->getAllStatusCodes();
        $this->template->userPermissions = $this->permissionManager->getByType(Permission::TYPE_USER);
        $this->template->systemPermissions = $this->permissionManager->getByType(Permission::TYPE_SYSTEM);
    }

    protected function startup()
    {
        parent::startup();
        $this->setLevelCaptions(["1" => ["caption" => $this->translator->translate("settings.setting", 2), "link" => $this->link(":Setting:Default:")]]);
        $this->template->addFilter("typeColor", function ($type) {
            $color = $this->supplier->getEventColors();
            return $color[$type];
        });
    }

}