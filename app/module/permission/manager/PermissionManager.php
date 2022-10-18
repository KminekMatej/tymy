<?php

namespace Tymy\Module\Permission\Manager;

use Nette\Database\IRow;
use Nette\Database\Table\Selection;
use Nette\Utils\Strings;
use Tymy\Module\Authorization\Manager\AuthorizationManager;
use Tymy\Module\Core\Factory\ManagerFactory;
use Tymy\Module\Core\Manager\BaseManager;
use Tymy\Module\Core\Model\BaseModel;
use Tymy\Module\Permission\Mapper\PermissionMapper;
use Tymy\Module\Permission\Model\Permission;
use Tymy\Module\Permission\Model\Privilege;
use Tymy\Module\User\Model\User;

/**
 * Description of PermissionManager
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 2. 9. 2020
 */
class PermissionManager extends BaseManager
{
    private ?Permission $permission = null;

    public function __construct(ManagerFactory $managerFactory, private AuthorizationManager $authorizationManager)
    {
        parent::__construct($managerFactory);
    }

    protected function getClassName(): string
    {
        return Permission::class;
    }

    /**
     * @return \Tymy\Module\Core\Model\Field[]
     */
    protected function getScheme(): array
    {
        return PermissionMapper::scheme();
    }

    public function map(?IRow $row, $force = false): ?BaseModel
    {
        if (empty($row)) {
            return null;
        }

        /* @var $permission Permission */
        $permission = parent::map($row, $force);

        $permission->setWebname(Strings::webalize($permission->getId() . "-" . $permission->getName()));

        return $permission;
    }

    protected function metaMap(BaseModel &$model, $userId = null): void
    {
        $privilege = $model->getType() == Permission::TYPE_SYSTEM ? Privilege::SYS($model->getName()) : Privilege::USR($model->getName());
        $model->setMeAllowed($this->user->isLoggedIn() && $this->user->isAllowed($this->user->getId(), $privilege));
    }

    public function canEdit($entity, $userId): bool
    {
        //todo
        return false;
    }

    public function canRead($entity, $userId): bool
    {
        return false; //todo
    }

    public function getAllowedReaders(BaseModel $record): array
    {
        return false; //todo
    }

    /**
     * Find permissions by its name - returns the first one that matches
     */
    public function getByTypeName(string $type, string $name): ?\Tymy\Module\Core\Model\BaseModel
    {
        return $this->map($this->database->table($this->getTable())->where("right_type", $type)->where("name", $name)->limit(1)->fetch());
    }

    /**
     * Find permissions by its name - returns the first one that matches
     */
    public function getByName(string $name): ?\Tymy\Module\Core\Model\BaseModel
    {
        return $this->map($this->database->table($this->getTable())->where("name", $name)->limit(1)->fetch());
    }

    /**
     * Find permissions by its type
     * @return Permission[]
     */
    public function getByType(string $type): array
    {
        return $this->mapAll($this->database->table($this->getTable())->where("right_type", $type)->fetchAll());
    }

    /**
     * Get all permission names which are allowed for user
     * @return array of names
     */
    public function getUserAllowedPermissionNames(User $user, ?string $type = null): array
    {
        return $this->getUserAllowedPermissions($user, $type)->fetchPairs(null, "name");
    }

    /**
     * Get all permission objects which are allowed for user
     * @return Permission[]
     */
    public function getUserAllowedPermissionObjects(User $user, ?string $type = null): array
    {
        return $this->mapAll($this->getUserAllowedPermissions($user, $type)->fetchAll());
    }

    /**
     * Get all permissions which are allowed for user
     */
    public function getUserAllowedPermissions(User $user, ?string $type = null): Selection
    {
        $userId = $user->getId();
        $roles = $user->getRoles();
        $status = $user->getStatus();

        $selector = $this->database->table($this->getTable());
        $conditions = [];
        $params = [];
        foreach ($roles as $allowedRole) {
            $conditions[] = "FIND_IN_SET(?, a_roles) > 0";
            $params[] = "$allowedRole";
        }

        $conditions[] = "FIND_IN_SET(?, a_statuses) > 0";
        $params[] = "$status";

        $conditions[] = "FIND_IN_SET(?, a_users) > 0";
        $params[] = "$userId";

        $selector->where("(" . implode(") OR (", $conditions) . ")", ...$params);

        //add revokes
        foreach ($roles as $revokedRole) {
            $selector->where("(FIND_IN_SET(?, r_roles) = 0 OR r_roles IS NULL)", "$revokedRole");
        }

        $selector->where("FIND_IN_SET(?, r_statuses) = 0 OR r_statuses IS NULL", "$status");
        $selector->where("FIND_IN_SET(?, r_users) = 0 OR r_users IS NULL", "$userId");

        if ($type) {
            $selector->where("right_type", $type);
        }

        return $selector;
    }

    protected function allowCreate(?array &$data = null): void
    {
        $this->allowAdmin();

        $data["type"] = Permission::TYPE_USER; //new permissions must always be USR permissions

        $this->checkInputs($data);

        $this->respondBadRequest("Name already used");

        $this->precedenceCheck($data);
    }

    protected function allowDelete(?int $recordId): void
    {
        $this->allowAdmin();

        $this->permission = $this->getById($recordId);

        if (!$this->permission instanceof \Tymy\Module\Core\Model\BaseModel) {
            $this->respondNotFound();
        }

        if ($this->permission->getType() !== Permission::TYPE_USER) {
            $this->respondForbidden();
        }
    }

    protected function allowRead(?int $recordId = null): void
    {
        $this->allowAdmin();

        $this->permission = $this->getById($recordId);
    }

    protected function allowUpdate(?int $recordId = null, ?array &$data = null): void
    {
        $this->allowAdmin();

        $this->permission = $this->getById($recordId);

        if (!$this->permission instanceof \Tymy\Module\Core\Model\BaseModel) {
            $this->respondNotFound();
        }

        if (isset($data["name"]) && $data["name"] !== $this->permission->getName()) {
            $namedPermission = $this->getByName($data["name"]);
            if ($namedPermission->getId() !== $this->permission->getId()) {
                $this->respondBadRequest("Name already used");
            }
        }

        if ($this->permission->getType() !== Permission::TYPE_USER) {
            unset($data["caption"]);
            unset($data["name"]);  //caption and name cannot be edited for other types than USR
        }

        $this->precedenceCheck($data);
    }

    /**
     * Transform input data passed as array of strings, to one string, comma separated (which is what database wants)
     */
    private function transformArrayToString(array &$data): void
    {
        $inputsToProcess = [
            "allowedRoles",
            "revokedRoles",
            "allowedStatuses",
            "revokedStatuses",
            "allowedUsers",
            "revokedUsers",
        ];
        foreach ($inputsToProcess as $input) {
            if (array_key_exists($input, $data) && is_array($data[$input])) {
                    $data[$input] = implode(",", $data[$input]);
            }
        }

        //when there is revoked set, set allowed to be cleared. If revoked is not set,
        foreach (["Roles", "Statuses", "Users"] as $Appendix) {
            $allowedKey = "allowed$Appendix";
            $revokedKey = "revoked$Appendix";
            if (array_key_exists($revokedKey, $data)) {   //if there is some revokation set, clear allowed
                $data[$allowedKey] = null;
            } elseif (array_key_exists($allowedKey, $data)) {
                $data[$revokedKey] = null;
            }
        }
    }

    public function create(array $data, ?int $resourceId = null): BaseModel
    {
        $this->allowCreate($data);

        $this->transformArrayToString($data);

        $createdRow = parent::createByArray($data);

        $this->authorizationManager->dropPermissionCache();

        return $this->map($createdRow);
    }

    public function delete(int $resourceId, ?int $subResourceId = null): int
    {
        $this->allowDelete($resourceId);

        $deleted = parent::deleteRecord($resourceId);

        $this->authorizationManager->dropPermissionCache();

        return $deleted !== 0 ? $resourceId : null;
    }

    public function read(int $resourceId, ?int $subResourceId = null): BaseModel
    {
        $this->allowRead($resourceId);

        return $this->permission;
    }

    public function update(array $data, int $resourceId, ?int $subResourceId = null): BaseModel
    {
        $this->allowUpdate($resourceId, $data);

        $this->transformArrayToString($data);
        \Tracy\Debugger::barDump($data);
        parent::updateByArray($resourceId, $data);

        $this->authorizationManager->dropPermissionCache();

        return $this->getById($resourceId);
    }

    /**
     * Drop allowed*** inputs in requestData if there are some revoked**** set.
     * If no revokes are detected, then it drops allowed inputs
     */
    private function precedenceCheck(array &$data): void
    {
        // make sure that revoked takes precedence before allowed - so drop anything allowed when something is set to revoked
        if (isset($data["revokedRoles"])) {
            unset($data["allowedRoles"]);
        }

        if (isset($data["revokedStatuses"])) {
            unset($data["allowedStatuses"]);
        }

        if (isset($data["revokedUsers"])) {
            unset($data["allowedUsers"]);
        }


        // if there are only allowances sent - automatically drop revokes
        if (isset($data["allowedRoles"])) {
            unset($data["revokedRoles"]);
        }

        if (isset($data["allowedStatuses"])) {
            unset($data["revokedStatuses"]);
        }

        if (isset($data["allowedUsers"])) {
            unset($data["revokedUsers"]);
        }
    }

    public function getByWebName(string $webname): ?Permission
    {
        $permissions = $this->getList();

        foreach ($permissions as $permission) {
            /* @var $permission Permission */
            if ($permission->getWebname() == $webname) {
                return $permission;
            }
        }

        return null;
    }
}
