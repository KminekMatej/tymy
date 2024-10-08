<?php

namespace Tymy\Module\Discussion\Manager;

use Nette\Database\IRow;
use Nette\Utils\Strings;
use Tymy\Module\Core\Factory\ManagerFactory;
use Tymy\Module\Core\Manager\BaseManager;
use Tymy\Module\Core\Model\BaseModel;
use Tymy\Module\Core\Model\Field;
use Tymy\Module\Discussion\Mapper\DiscussionMapper;
use Tymy\Module\Discussion\Model\Discussion;
use Tymy\Module\Discussion\Model\NewInfo;
use Tymy\Module\Permission\Manager\PermissionManager;
use Tymy\Module\Permission\Model\Permission;
use Tymy\Module\User\Manager\UserManager;

/**
 * @extends BaseManager<Discussion>
 */
class DiscussionManager extends BaseManager
{
    /** @var Discussion|null */
    private ?Discussion $discussion = null;

    public function __construct(ManagerFactory $managerFactory, private PermissionManager $permissionManager, private UserManager $userManager)
    {
        parent::__construct($managerFactory);
    }

    protected function allowCreate(?array &$data = null): void
    {
        if (!$this->user->isAllowed((string) $this->user->getId(), "SYS:DSSETUP")) {
            $this->respondForbidden();
        }
    }

    protected function allowDelete(?int $recordId): void
    {
        if (!$this->user->isAllowed((string) $this->user->getId(), "SYS:DSSETUP")) {
            $this->respondForbidden();
        }
    }

    protected function allowRead(?int $recordId = null): void
    {
        if ($recordId) {
            $this->discussion = $this->loadRecord($recordId);
            assert($this->discussion instanceof Discussion);

            if (!$this->discussion->getCanRead()) {
                $this->responder->E4001_VIEW_NOT_PERMITTED(Discussion::MODULE, $recordId);
            }
        }
    }

    protected function allowUpdate(?int $recordId = null, ?array &$data = null): void
    {
        if (!$this->user->isAllowed((string) $this->user->getId(), "SYS:DSSETUP")) {
            $this->respondForbidden();
        }
    }

    /**
     * Maps one active row to object
     *
     * @param IRow|null $row
     * @param bool $force True to skip cache
     * @return Discussion|null
     */
    public function map(?IRow $row, bool $force = false): ?BaseModel
    {
        if ($row === null) {
            return null;
        }

        $discussion = parent::map($row, $force);
        assert($discussion instanceof Discussion);

        $discussion->setNewInfo(new NewInfo($discussion->getId(), $row->newInfo, $row->lastVisit));
        $discussion->setNumberOfPosts($row->numberOfPosts);
        $discussion->setWebName(Strings::webalize($discussion->getId() . "-" . $discussion->getCaption()));

        return $discussion;
    }

    protected function metaMap(BaseModel &$model, $userId = null): void
    {
        assert($model instanceof Discussion);
        $model->setCanRead(empty($model->getReadRightName()) || $this->user->isAllowed((string) $this->user->getId(), "USR:{$model->getReadRightName()}"));
        $model->setCanWrite(empty($model->getWriteRightName()) || $this->user->isAllowed((string) $this->user->getId(), "USR:{$model->getWriteRightName()}"));
        $model->setCanDelete($this->userManager->isAdmin() || (!empty($model->getDeleteRightName()) && $this->user->isAllowed((string) $this->user->getId(), "USR:{$model->getDeleteRightName()}")));
        $model->setCanStick($this->userManager->isAdmin() || (!empty($model->getStickyRightName()) && $this->user->isAllowed((string) $this->user->getId(), "USR:{$model->getStickyRightName()}")));
    }

    public function getById(int $id, bool $force = false): ?BaseModel
    {
        return $this->map($this->database->query("
            SELECT `discussion`.*, `discussion_read`.`last_date` AS `lastVisit`, 
            (
                SELECT COUNT(`discussion_post`.`id`) 
                FROM `discussion_post` 
                WHERE `discussion_post`.`insert_date` > `discussion_read`.`last_date` AND `discussion_post`.`discussion_id` = `discussion`.`id`
            ) AS `newInfo`, 
            (
                SELECT COUNT(`discussion_post`.`id`) 
                FROM `discussion_post` 
                WHERE `discussion_post`.`discussion_id` = `discussion`.`id`
            ) AS `numberOfPosts` 
            FROM `discussion` 
            LEFT JOIN `discussion_read` ON `discussion`.`id` = `discussion_read`.`discussion_id` AND
            (`discussion_read`.`discussion_id`=`discussion`.`id`) AND (`discussion_read`.`user_id` = ?) 
            WHERE `discussion`.`id` = ? ORDER BY `discussion`.`order_flag` ASC", $this->user->getId(), $id)->fetch());
    }

    /**
     * Get discussion object using its webname, optionally with check for user permissions
     */
    public function getByWebName(string $webName, ?int $userId = null): ?Discussion
    {
        $discussionList = $userId ? $this->getListUserAllowed($userId) : $this->getList();

        foreach ($discussionList as $discussion) {
            assert($discussion instanceof Discussion);
            if ($discussion->getWebName() == $webName) {
                return $discussion;
            }
        }

        return null;
    }

    /**
     * Get array of discussion objects which user is allowed to read
     *
     * @return Discussion[]
     */
    public function getListUserAllowed(int $userId): array
    {
        $readPerms = $this->permissionManager->getUserAllowedPermissionNames($this->userManager->getById($this->user->getId()), Permission::TYPE_USER);
        $readPermsQ = empty($readPerms) ? "" : "`discussion`.`read_rights` IN (?) OR";
        $query = "
            SELECT `discussion`.*, `discussion_read`.`last_date` AS `lastVisit`, 
            (
                SELECT COUNT(`discussion_post`.`id`) 
                FROM `discussion_post` 
                WHERE `discussion_post`.`insert_date` > `discussion_read`.`last_date` AND `discussion_post`.`discussion_id` = `discussion`.`id`
            ) AS `newInfo`, 
            (
                SELECT COUNT(`discussion_post`.`id`) 
                FROM `discussion_post` 
                WHERE `discussion_post`.`discussion_id` = `discussion`.`id`
            ) AS `numberOfPosts` 
            FROM `discussion` 
            LEFT JOIN `discussion_read` ON `discussion`.`id` = `discussion_read`.`discussion_id` AND
            (`discussion_read`.`discussion_id`=`discussion`.`id`) AND (`discussion_read`.`user_id` = ?) 
            WHERE ($readPermsQ `discussion`.`read_rights` IS NULL OR
            TRIM(`discussion`.`read_rights`) = '') ORDER BY `discussion`.`order_flag` ASC";
        $selector = empty($readPerms) ? $this->database->query($query, $userId) : $this->database->query($query, $userId, $readPerms);
        return $this->mapAll($selector->fetchAll());
    }

    /**
     * @return BaseModel[]
     */
    public function getList(?array $idList = null, string $idField = "id", ?int $limit = null, ?int $offset = null, ?string $order = null): array
    {
        $query = "
            SELECT `discussion`.*, `discussion_read`.`last_date` AS `lastVisit`, 
            (
                SELECT COUNT(`discussion_post`.`id`) 
                FROM `discussion_post` 
                WHERE `discussion_post`.`insert_date` > `discussion_read`.`last_date` AND `discussion_post`.`discussion_id` = `discussion`.`id`
            ) AS `newInfo`, 
            (
                SELECT COUNT(`discussion_post`.`id`) 
                FROM `discussion_post` 
                WHERE `discussion_post`.`discussion_id` = `discussion`.`id`
            ) AS `numberOfPosts` 
            FROM `discussion` 
            LEFT JOIN `discussion_read` ON `discussion`.`id` = `discussion_read`.`discussion_id` AND
            (`discussion_read`.`discussion_id`=`discussion`.`id`) AND (`discussion_read`.`user_id` = ?) 
            WHERE 1 ORDER BY `discussion`.`order_flag` ASC";
        return $this->mapAll($this->database->query($query, $this->user->getId())->fetchAll());
    }

    /**
     * Get array of discussion ids which user is allowed to read
     *
     * @return int[]
     */
    public function getIdsUserAllowed(int $userId): array
    {
        $readPerms = $this->permissionManager->getUserAllowedPermissionNames($this->userManager->getById($userId), Permission::TYPE_USER);
        return $this->database->table($this->getTable())->where("read_rights IS NULL OR read_rights = '' OR read_rights IN (?)", $readPerms)->fetchPairs(null, "id");
    }

    public function getClassName(): string
    {
        return Discussion::class;
    }

    /**
     * @return Field[]
     */
    public function getScheme(): array
    {
        return DiscussionMapper::scheme();
    }

    /**
     * Check edit permission
     *
     * @param Discussion $entity
     */
    public function canEdit($entity, int $userId): bool
    {
        return in_array($userId, $this->userManager->getUserIdsWithPrivilege("SYS:DSSETUP"));
    }

    /**
     * Check read permission
     *
     * @param Discussion $entity
     */
    public function canRead($entity, int $userId): bool
    {
        return in_array($entity->getId(), $this->getIdsUserAllowed($userId));
    }

    /**
     * Get user ids allowed to read given discussion
     *
     * @param Discussion $record
     * @return int[]|mixed[]
     */
    public function getAllowedReaders(BaseModel $record): array
    {
        assert($record instanceof Discussion);
        if (empty($record->getReadRightName())) {
            return $this->getAllUserIds();
        }

        return $this->userManager->getUserIdsWithPrivilege("USR:{$record->getReadRightName()}");
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
        parent::toBoolData($data, ["publicRead", "editablePosts"]);

        $this->allowUpdate($resourceId);

        parent::updateByArray($resourceId, $data);

        return $this->getById($resourceId);
    }

    /**
     * Get sum of all warnings of desired discussions
     *
     * @param Discussion[] $discussions
     */
    public function getWarnings(array $discussions): int
    {
        $count = 0;
        foreach ($discussions as $discussion) {
            assert($discussion instanceof Discussion);
            $count += $discussion->getNewInfo()->getNewsCount();
        }

        return $count;
    }
}
