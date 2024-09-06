<?php

namespace Tymy\Module\Authorization\Manager;

use Nette\Database\Explorer;
use Nette\Database\Table\ActiveRow;
use Nette\Security\IAuthorizator;
use stdClass;
use Tymy\Module\Core\Model\Field;
use Tymy\Module\Permission\Mapper\PermissionMapper;
use Tymy\Module\Permission\Model\Permission;
use Tymy\Module\User\Model\User;

/**
 * Description of AuthorizationManager
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 2. 9. 2020
 */
class AuthorizationManager implements IAuthorizator
{
    private array $permissionCache = [];
    private array $userCache = [];

    public function __construct(private Explorer $teamDatabase)
    {
    }

    private function getUserStatus($userId): string
    {
        if (!array_key_exists($userId, $this->userCache)) {
            $userStatus = $this->teamDatabase->table(User::TABLE)->where("id", $userId)->fetch()["status"];
            $this->userCache[$userId] = $userStatus;
        }

        return $this->userCache[$userId];
    }

    public function dropPermissionCache(): void
    {
        $this->permissionCache = [];
    }

    /**
     * Maps one active row to object
     */
    public function map(string $class, array $scheme, ActiveRow|false $row, $force = false): ?object
    {
        if (!$row) {
            return null;
        }

        $object = new $class();

        foreach ($scheme as $field) {
            assert($field instanceof Field);
            $setField = "set" . ucfirst($field->getProperty());
            $column = $field->getColumn();

            if ($row->$column === null && !$field->getMandatory()) {
                continue; //non-mandatory field dont need to set to null, again
            }
            $object->$setField($row->$column);
        }

        return $object; // if this function is called from children, do not postMap (children should do postmap instead)
    }

    /**
     * Find permissions by its name - returns the first one that matches
     */
    public function getPermission(string $type, string $name): ?Permission
    {
        if (!array_key_exists($type, $this->permissionCache)) {
            $this->permissionCache[$type] = [];
            $typePermissions = $this->teamDatabase->table(Permission::TABLE)->where("right_type", $type)->fetchAll();
            foreach ($typePermissions as $typePermissionRow) {
                $permission = $typePermissionRow ? $this->map(Permission::class, PermissionMapper::scheme(), $typePermissionRow) : null;
                assert($permission instanceof Permission);
                $this->permissionCache[$type][$permission->getName()] = $permission;
            }
        }

        return $this->permissionCache[$type][$name] ?? null;
    }

    /**
     * Check user is allowed for privilege. Does the same thing as Nette user->isAllowed() but with any user object
     */
    public function isUserAllowed(User $user, ?string $privilege): bool
    {
        foreach ($user->getRoles() as $role) {
            if ($this->isAllowed($role, $user->getId(), $privilege)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Main permissions checker
     * @param string $role Role - SUPER / USR / WEB / ATT
     * @param string|null $resource User id
     * @param string|null $privilege Privilege, consisting of type and name of permissions
     */
    public function isAllowed(?string $role, ?string $resource, ?string $privilege): bool
    {
        if (!$privilege) {
            //\Tracy\Debugger::barDump("No privilege");
            return self::Deny;
        }

        //\Tracy\Debugger::barDump("Checking role $role for user id $resource and privilege {$privilege->getType()}:{$privilege->getName()}");

        $privParts = explode(":", $privilege);
        $type = array_shift($privParts);
        $name = join(":", $privParts); //join by colon back in case some user permission would contain it

        if ($type == "SYS") {
            if ($name == "IS_ADMIN") {
                return $this->isAdmin($role);
            }
            if ($name == "SEE_INITS") {
                return in_array($role, ["SUPER", "USR"]) ? self::Allow : self::Deny;
            }

            if ($this->isAdmin($role)) {
                return self::Allow;
            }
        }

        $permission = $this->getPermission($type, $name);
        if (!$permission instanceof Permission) {
            //\Tracy\Debugger::log("No permission");
            return self::Deny;
        }
        //\Tracy\Debugger::log("Allowed by role: " . ($this->isAllowedByRole($role, $permission) ? "true" : "false"));
        //\Tracy\Debugger::log("Allowed by status: " . ($this->isAllowedByStatus($this->getUserStatus($resource), $permission) ? "true" : "false"));
        //\Tracy\Debugger::log("Allowed by id: " . ($this->isAllowedById($resource, $permission) ? "true" : "false"));
        return $this->isAllowedByRole($role, $permission) || $this->isAllowedByStatus($this->getUserStatus($resource), $permission) || $this->isAllowedById($resource, $permission) ? self::Allow : self::Deny;
    }

    private function isAdmin(string $role): bool
    {
        return $role == "SUPER" ? self::Allow : self::Deny;
    }

    private function isAllowedByRole(string $role, Permission $permission): bool
    {
        return is_array($permission->getAllowedRoles()) && in_array($role, $permission->getAllowedRoles()) && (empty($permission->getRevokedRoles()) || !in_array($role, $permission->getRevokedRoles()));
    }

    private function isAllowedByStatus(string $status, Permission $permission): bool
    {
        return is_array($permission->getAllowedStatuses()) && in_array($status, $permission->getAllowedStatuses()) && (empty($permission->getRevokedStatuses()) || !in_array($status, $permission->getRevokedStatuses()));
    }

    private function isAllowedById(int $id, Permission $permission): bool
    {
        return is_array($permission->getAllowedUsers()) && in_array($id, $permission->getAllowedUsers()) && (empty($permission->getRevokedUsers()) || !in_array($id, $permission->getRevokedUsers()));
    }

    public function getListUserAllowed(User $user): stdClass
    {
        return (object)[
            "notesRights" => $this->getNotesRights($user),
            "discussionRights" => $this->getDiscussionRights($user),
            "eventRights" => $this->getEventRights($user),
            "pollRights" => $this->getPollRights($user),
            "reportsRights" => $this->getReportsRights($user),
            "teamRights" => $this->getTeamRights($user),
            "userRights" => $this->getUserRights($user),
            "debtRights" => $this->getDebtRights($user),
        ];
    }

    private function getNotesRights(User $user): stdClass
    {
        return (object) [
                    "manageSharedNotes" => $this->isUserAllowed($user, "SYS:NOTES")
        ];
    }

    private function getDiscussionRights(User $user): stdClass
    {
        return (object) [
                    "setup" => $this->isUserAllowed($user, "SYS:DSSETUP")
        ];
    }

    private function getEventRights(User $user): stdClass
    {
        return (object) [
                    "canCreate" => $this->isUserAllowed($user, "SYS:EVE_CREATE"),
                    "canDelete" => $this->isUserAllowed($user, "SYS:EVE_DELETE"),
                    "canUpdate" => $this->isUserAllowed($user, "SYS:EVE_UPDATE"),
                    "canResult" => $this->isUserAllowed($user, "SYS:EVE_ATT_UPDATE"),
                    "canPlanOthers" => $this->isUserAllowed($user, "SYS:ATT_UPDATE"),
        ];
    }

    private function getPollRights(User $user): stdClass
    {
        return (object) [
                    "canCreatePoll" => $this->isUserAllowed($user, "SYS:ASK.VOTE_CREATE"),
                    "canUpdatePoll" => $this->isUserAllowed($user, "SYS:ASK.VOTE_UPDATE"),
                    "canDeletePoll" => $this->isUserAllowed($user, "SYS:ASK.VOTE_DELETE"),
                    "canResetVotes" => $this->isUserAllowed($user, "SYS:ASK.VOTE_RESET"),
        ];
    }

    private function getReportsRights(User $user): stdClass
    {
        return (object) [
                    "canSetup" => $this->isUserAllowed($user, "SYS:REP_SETUP")
        ];
    }

    private function getTeamRights(User $user): stdClass
    {
        return (object) [
                    "canSetup" => $this->isUserAllowed($user, "SYS:TEAM_UPDATE")
        ];
    }

    private function getUserRights(User $user): stdClass
    {
        return (object) [
                    "canCreate" => $this->isUserAllowed($user, "SYS:USR_CREATE"),
                    "canUpdate" => $this->isUserAllowed($user, "SYS:USR_UPDATE"),
                    "canDelete" => $this->isUserAllowed($user, "SYS:USR_HDEL"),
        ];
    }

    private function getDebtRights(User $user): stdClass
    {
        return (object) [
                    "canManageTeamDebts" => $this->isUserAllowed($user, "SYS:DEBTS_TEAM")
        ];
    }
}
