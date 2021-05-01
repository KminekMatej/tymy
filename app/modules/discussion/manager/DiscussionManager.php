<?php

namespace Tymy\Module\Discussion\Manager;

use Nette\Database\IRow;
use Nette\Database\Table\ActiveRow;
use Nette\Utils\Strings;
use Tymy\Module\Core\Factory\ManagerFactory;
use Tymy\Module\Core\Manager\BaseManager;
use Tymy\Module\Core\Model\BaseModel;
use Tymy\Module\Discussion\Mapper\DiscussionMapper;
use Tymy\Module\Discussion\Model\Discussion;
use Tymy\Module\Discussion\Model\NewInfo;
use Tymy\Module\Permission\Manager\PermissionManager;
use Tymy\Module\Permission\Model\Permission;
use Tymy\Module\Permission\Model\Privilege;
use Tymy\Module\User\Manager\UserManager;

/**
 * Description of DiscussionManager
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 5. 6. 2020
 */
class DiscussionManager extends BaseManager
{
    private PermissionManager $permissionManager;
    private UserManager $userManager;
    private ?Discussion $discussion;

    public function __construct(ManagerFactory $managerFactory, PermissionManager $permissionManager, UserManager $userManager)
    {
        parent::__construct($managerFactory);
        $this->permissionManager = $permissionManager;
        $this->userManager = $userManager;
    }

    protected function allowCreate(?array &$data = null): void
    {
        if (!$this->user->isAllowed($this->user->getId(), Privilege::SYS("DSSETUP"))) {
            $this->respondForbidden();
        }
    }

    protected function allowDelete(?int $recordId): void
    {
        if (!$this->user->isAllowed($this->user->getId(), Privilege::SYS("DSSETUP"))) {
            $this->respondForbidden();
        }
    }

    protected function allowRead(?int $recordId = null): void
    {
        if ($recordId) {
            $this->discussion = $this->loadRecord($recordId);

            if (!$this->discussion->getCanRead()) {
                $this->responder->E4001_VIEW_NOT_PERMITTED(Discussion::MODULE, $recordId);
            }
        }
    }

    protected function allowUpdate(?int $recordId = null, ?array &$data = null): void
    {
        if (!$this->user->isAllowed($this->user->getId(), Privilege::SYS("DSSETUP"))) {
            $this->respondForbidden();
        }
    }

    /**
     * Maps one active row to object
     * @param ActiveRow|false $row
     * @param bool $force True to skip cache
     * @return Discussion|null
     */
    public function map(?IRow $row, bool $force = false): ?BaseModel
    {
        if (!$row) {
            return null;
        }

        /* @var $discussion Discussion */
        $discussion = parent::map($row, $force);

        $discussion->setNewInfo(new NewInfo($discussion->getId(), $row->newInfo, $row->lastVisit));
        $discussion->setWebName(Strings::webalize($discussion->getCaption()));

        return $discussion;
    }

    protected function metaMap(BaseModel &$model, $userId = null): void
    {
        /* @var $model Discussion */
        $model->setCanRead(empty($model->getReadRightName()) || $this->user->isAllowed($this->user->getId(), Privilege::USR($model->getReadRightName())));
        $model->setCanWrite(empty($model->getWriteRightName()) || $this->user->isAllowed($this->user->getId(), Privilege::USR($model->getWriteRightName())));
        $model->setCanDelete($this->user->isAllowed($this->user->getId(), Privilege::USR($model->getDeleteRightName())));
        $model->setCanStick($this->user->isAllowed($this->user->getId(), Privilege::USR($model->getStickyRightName())));
    }

    public function getById(int $id, bool $force = false): ?BaseModel
    {
        return $this->map($this->database->query("
            SELECT `discussions`.*, `ds_read`.`last_date` AS `lastVisit`, 
            (
                SELECT COUNT(`ds_items`.`id`) 
                FROM `ds_items` 
                WHERE `ds_items`.`insert_date` > `ds_read`.`last_date` AND `ds_items`.`ds_id` = `discussions`.`id`
            ) AS `newInfo` 
            FROM `discussions` 
            LEFT JOIN `ds_read` ON `discussions`.`id` = `ds_read`.`ds_id` AND
            (`ds_read`.`ds_id`=`discussions`.`id`) AND (`ds_read`.`user_id` = ?) 
            WHERE `discussions`.`id` = ?", $this->user->getId(), $id)->fetch());
    }

    /**
     * Get discussion object using its webname
     * 
     * @param string $webName
     * @return Discussion|null
     */
    public function getByWebName(string $webName): ?Discussion
    {
        $discussionList = $this->getList();

        foreach ($discussionList as $discussion) {
            /* @var $discussion Discussion */
            if ($discussion->getWebName() == $webName) {
                return $discussion;
            }
        }

        return null;
    }

    /**
     * Get array of discussion objects which user is allowed to read
     * @param int $userId
     * @return Discussion[]
     */
    public function getListUserAllowed($userId)
    {
        $readPerms = $this->permissionManager->getUserAllowedPermissionNames($this->userManager->getById($this->user->getId()), Permission::TYPE_USER);
        $readPermsQ = empty($readPerms) ? "" : "`discussions`.`read_rights` IN (?) OR";
        $query = "
            SELECT `discussions`.*, `ds_read`.`last_date` AS `lastVisit`, 
            (
                SELECT COUNT(`ds_items`.`id`) 
                FROM `ds_items` 
                WHERE `ds_items`.`insert_date` > `ds_read`.`last_date` AND `ds_items`.`ds_id` = `discussions`.`id`
            ) AS `newInfo` 
            FROM `discussions` 
            LEFT JOIN `ds_read` ON `discussions`.`id` = `ds_read`.`ds_id` AND
            (`ds_read`.`ds_id`=`discussions`.`id`) AND (`ds_read`.`user_id` = ?) 
            WHERE ($readPermsQ `discussions`.`read_rights` IS NULL OR
            TRIM(`discussions`.`read_rights`) = '')";
        $selector = empty($readPerms) ? $this->database->query($query, $userId) : $this->database->query($query, $userId, $readPerms ?: "");
        return $this->mapAll($selector->fetchAll());
    }

    /**
     * Get array of discussion ids which user is allowed to read
     * @param int $userId
     * @return int[]
     */
    public function getIdsUserAllowed($userId)
    {
        $readPerms = $this->permissionManager->getUserAllowedPermissionNames($this->userManager->getById($userId), Permission::TYPE_USER);
        return $this->database->table($this->getTable())->where("read_rights IS NULL OR read_rights = '' OR read_rights IN (?)", $readPerms)->fetchPairs(null, "id");
    }

    protected function getClassName(): string
    {
        return Discussion::class;
    }

    protected function getScheme(): array
    {
        return DiscussionMapper::scheme();
    }

    /**
     * Check edit permission
     * @param Discussion $entity
     * @param int $userId
     * @return bool
     */
    public function canEdit($entity, $userId): bool
    {
        return in_array($userId, $this->userManager->getUserIdsWithPrivilege(Privilege::SYS("DSSETUP")));
    }

    /**
     * Check read permission
     * @param Discussion $entity
     * @param int $userId
     * @return bool
     */
    public function canRead($entity, $userId): bool
    {
        return in_array($entity->getId(), $this->getIdsUserAllowed($userId));
    }

    /**
     * Get user ids allowed to read given discussion
     * @param Discussion $record
     * @return int[]
     */
    public function getAllowedReaders(BaseModel $record): array
    {
        /* @var $record Discussion */
        return $this->userManager->getUserIdsWithPrivilege(Privilege::USR($record->getReadRightName()));
    }

    public function create(array $data, ?int $resourceId = null): BaseModel
    {
        $this->allowCreate($data);

        $createdRow = parent::createByArray($data);

        return $this->getById($createdRow->id);
    }

    public function delete(int $resourceId, ?int $subResourceId = null): int
    {
        $this->allowDelete($resourceId);

        return parent::deleteRecord($resourceId);
    }

    public function read(int $resourceId, ?int $subResourceId = null): BaseModel
    {
        $this->allowRead($resourceId);

        return $this->getById($resourceId);
    }

    public function update(array $data, int $resourceId, ?int $subResourceId = null): BaseModel
    {
        $this->allowUpdate($resourceId);

        parent::updateByArray($resourceId, $data);

        return $this->getById($resourceId);
    }

    /**
     * Get sum of all warnings of desired discussions
     * 
     * @param Discussion[] $discussions
     * @return int
     */
    public function getWarnings(array $discussions): int
    {
        $count = 0;
        foreach ($discussions as $discussion) {
            /* @var $discussion Discussion */
            $count += $discussion->getNewInfo()->getNewsCount();
        }

        return $count;
    }
}