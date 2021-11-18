<?php

namespace Tymy\Module\Setting\Presenter\Front;

use Nette\Utils\Strings;
use Tymy\Module\Permission\Manager\PermissionManager;
use Tymy\Module\Permission\Model\Permission;
use Tymy\Module\Permission\Model\Privilege;
use Tymy\Module\Setting\Presenter\Front\SettingBasePresenter;

class PermissionPresenter extends SettingBasePresenter
{

    /** @inject */
    public PermissionManager $permissionManager;

    public function actionPermissions($permission = NULL)
    {
        $this->allowPermission('IS_ADMIN');

        if (!is_null($permission)) {
            $this->setView("permission");
        } else {
            $this->setLevelCaptions(["2" => ["caption" => $this->translator->translate("permission.permission", 2), "link" => $this->link(":Setting:Permission:")]]);
        }
    }

    public function renderNew()
    {
        $this->allowPermission("IS_ADMIN");

        $this->setLevelCaptions([
            "2" => ["caption" => $this->translator->translate("permission.permission", 2), "link" => $this->link(":Setting:Permission:")],
            "3" => ["caption" => $this->translator->translate("permission.newPermission")]
        ]);
        $this->template->isNew = true;

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

        $this->setView("permission");
    }

    public function renderPermission($permission)
    {
        $this->allowPermission("IS_ADMIN");

        $perm = $this->permissionManager->getByWebName($permission);
        if ($perm == NULL) {
            $this->flashMessage($this->translator->translate("permission.errors.permissionNotExists", NULL, ['id' => $permission]), "danger");
            $this->redirect(':Setting:Event:');
        }

        $this->setLevelCaptions([
            "2" => ["caption" => $this->translator->translate("permission.permission", 2), "link" => $this->link(":Setting:Permission:")],
            "3" => ["caption" => $perm->getName(), "link" => $this->link(":Setting:Permission:", $perm->getWebname())]
        ]);

        $users = $this->userManager->getIdList();

        $this->template->lastEditedUser = $users[$perm->getUpdatedById()] ?? null;
        $this->template->allowances = ["allowed" => "Povoleno", "revoked" => "Zakázáno"];
        $this->template->statuses = ["PLAYER" => "Hráč", "SICK" => "Marod", "MEMBER" => "Člen"];
        $this->template->roles = $this->getAllRoles();

        $this->template->rolesRule = empty($perm->getAllowedRoles()) && empty($perm->getRevokedRoles()) ? null : (empty($perm->getRevokedRoles()) ? "allowed" : "revoked");
        $this->template->statusesRule = empty($perm->getAllowedStatuses()) && empty($perm->getRevokedStatuses()) ? null : (empty($perm->getRevokedStatuses()) ? "allowed" : "revoked");
        $this->template->usersRule = empty($perm->getAllowedUsers()) && empty($perm->getRevokedUsers()) ? null : (empty($perm->getRevokedUsers()) ? "allowed" : "revoked");

        $this->template->users = $users;
        $this->template->perm = $perm;
        $this->template->isNew = false;
    }

    public function handlePermissionCreate()
    {
        $bind = $this->getRequest()->getPost();
        /* @var $createdPermission Permission */
        $createdPermission = $this->permissionManager->create($this->composePermissionData($bind["changes"]));

        $this->redirect(":Setting:Permission:", [Strings::webalize($createdPermission->getName())]);
    }

    public function handlePermissionEdit()
    {
        $bind = $this->getRequest()->getPost();

        $data = $this->composePermissionData($bind["changes"]);

        $updatedPermission = $this->permissionManager->update($data, $bind["id"]);

        if (array_key_exists("name", $data)) {   //if name has been changed, redirect to a new name is neccessary
            $this->redirect(":Setting:Permission:", [Strings::webalize($updatedPermission->getName())]);
        }
    }

    public function handlePermissionDelete()
    {
        $bind = $this->getRequest()->getPost();
        $this->permissionManager->delete($bind["id"]);
    }

    /**
     * Create input array for permission, containing name, caption, allowedRoles (or revokedRoles), allowedStatuses (or revokedStatuses) and , allowedUsers (or revokedUsers)
     * @param array $changes
     * @return array
     */
    private function composePermissionData(array $changes): array
    {
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
                if (strpos($key, "userCheck") !== FALSE && $value == "true") {
                    $userList[] = (int) explode("_", $key)[1];
                }
            }
            $output[$changes["userAllowance"] == "allowed" ? "allowedUsers" : "revokedUsers"] = $userList;
        }

        return $output;
    }

}