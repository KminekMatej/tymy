<?php

namespace Tymy\Module\Setting\Presenter\Front;

use Nette\Utils\Strings;
use Tymy\Module\Core\Exception\TymyResponse;
use Tymy\Module\Permission\Manager\PermissionManager;
use Tymy\Module\Permission\Model\Permission;
use Tymy\Module\Setting\Presenter\Front\SettingBasePresenter;

class PermissionPresenter extends SettingBasePresenter
{
    /** @inject */
    public PermissionManager $permissionManager;

    public function beforeRender()
    {
        parent::beforeRender();
        $this->allowPermission('IS_ADMIN');
        $this->addBreadcrumb($this->translator->translate("permission.permission", 2), $this->link(":Setting:Permission:"));
    }

    public function actionDefault(?string $resource = null)
    {
        if ($resource) {
            $this->setView("permission");
        }
    }

    public function renderNew()
    {
        $this->allowPermission("IS_ADMIN");

        $this->addBreadcrumb($this->translator->translate("permission.newPermission"));

        $users = $this->userManager->getIdList();

        $perm = (new Permission())
                ->setId(-1)
                ->setName("")
                ->setCaption("")
                ->setType("USR");

        $this->template->allowances = ["allowed" => "Povoleno", "revoked" => "Zakázáno"];
        $this->template->statuses = ["PLAYER" => "Hráč", "SICK" => "Marod", "MEMBER" => "Člen"];
        $this->template->roles = $this->getAllRoles();
        $this->template->users = $users;
        $this->template->perm = $perm;

        $this->template->rolesRule = "revoked";
        $this->template->statusesRule = "revoked";
        $this->template->usersRule = "revoked";
    }

    public function renderPermission(?string $resource = null)
    {
        $this->allowPermission("IS_ADMIN");

        $permission = $this->permissionManager->getByWebName($resource);
        if (!$permission) {
            $this->flashMessage($this->translator->translate("permission.errors.permissionNotExists", null, ['id' => $permission]), "danger");
            $this->redirect(':Setting:Event:');
        }

        $this->addBreadcrumb($permission->getName(), $this->link(":Setting:Permission:", $permission->getWebname()));

        $users = $this->userManager->getIdList();

        $this->template->lastEditedUser = $users[$permission->getUpdatedById()] ?? null;
        $this->template->allowances = ["allowed" => $this->translator->translate("permission.allowed"), "revoked" => $this->translator->translate("permission.revoked")];
        $this->template->statuses = ["PLAYER" => $this->translator->translate("team.PLAYER", 1), "SICK" => $this->translator->translate("team.SICK", 1), "MEMBER" => $this->translator->translate("team.MEMBER", 1)];
        $this->template->roles = $this->getAllRoles();

        $this->template->rolesRule = empty($permission->getAllowedRoles()) && empty($permission->getRevokedRoles()) ? null : (empty($permission->getRevokedRoles()) ? "allowed" : "revoked");
        $this->template->statusesRule = empty($permission->getAllowedStatuses()) && empty($permission->getRevokedStatuses()) ? null : (empty($permission->getRevokedStatuses()) ? "allowed" : "revoked");
        $this->template->usersRule = empty($permission->getAllowedUsers()) && empty($permission->getRevokedUsers()) ? null : (empty($permission->getRevokedUsers()) ? "allowed" : "revoked");

        $this->template->users = $users;
        $this->template->perm = $permission;
        $this->template->isNew = false;
    }

    public function handlePermissionCreate()
    {
        $bind = $this->getRequest()->getPost();
        try {
            /* @var $createdPermission Permission */
            $createdPermission = $this->permissionManager->create($this->composePermissionData($bind["changes"]));
            $this->flashMessage($this->translator->translate("common.alerts.created"), 'success');
            $this->redirect(":Setting:Permission:", $createdPermission->getWebname());
        } catch (TymyResponse $tResp) {
            $this->handleTymyResponse($tResp);
        }
    }

    public function handlePermissionEdit()
    {
        $bind = $this->getRequest()->getPost();

        $data = $this->composePermissionData($bind["changes"]);
        try {
            $updatedPermission = $this->permissionManager->update($data, $bind["id"]);
            $this->flashMessage($this->translator->translate("common.alerts.updated"), 'success');
            if (array_key_exists("name", $data)) {   //if name has been changed, redirect to a new name is neccessary
                $this->redirect(":Setting:Permission:", $updatedPermission->getName());
            }
        } catch (TymyResponse $tResp) {
            $this->handleTymyResponse($tResp);
        }
    }

    public function handlePermissionDelete()
    {
        $bind = $this->getRequest()->getPost();

        try {
            $this->permissionManager->delete($bind["id"]);
            $this->flashMessage($this->translator->translate("common.alerts.deleted"), 'success');
            $this->redirect(":Setting:Permission:");
        } catch (TymyResponse $tResp) {
            $this->handleTymyResponse($tResp);
        }
    }

    /**
     * Create input array for permission, containing name, caption, allowedRoles (or revokedRoles), allowedStatuses (or revokedStatuses) and , allowedUsers (or revokedUsers)
     * @param array $changes
     * @return array
     */
    private function composePermissionData(array $changes): array
    {
        $output = [];

        if (array_key_exists("name", $changes)) {
            $output["name"] = $changes["name"];
        }
        if (array_key_exists("caption", $changes)) {
            $output["caption"] = $changes["caption"];
        }

        if (array_key_exists("roleAllowance", $changes)) { //set either allowed or revoked roles
            $roles = array_key_exists("roles", $changes) && is_array($changes["roles"]) ? $changes["roles"] : [];
            $output[$changes["roleAllowance"] == "allowed" ? "allowedRoles" : "revokedRoles"] = $roles;
        }

        if (array_key_exists("statusAllowance", $changes)) { //set either allowed or revoked statuses
            $statuses = array_key_exists("statuses", $changes) && is_array($changes["statuses"]) ? $changes["statuses"] : [];
            $output[$changes["statusAllowance"] == "allowed" ? "allowedStatuses" : "revokedStatuses"] = $statuses;
        }

        if (array_key_exists("userAllowance", $changes)) { //set either allowed or revoked users
            $userList = [];
            foreach ($changes as $key => $value) {
                if (strpos($key, "userCheck") !== false && $value == "true") {
                    $userList[] = (int) explode("_", $key)[1];
                }
            }
            $output[$changes["userAllowance"] == "allowed" ? "allowedUsers" : "revokedUsers"] = $userList;
        }

        return $output;
    }
}
