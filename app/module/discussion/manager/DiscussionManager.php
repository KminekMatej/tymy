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
use Tymy\Module\Discussion\Model\Post;
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
    private ?Discussion $discussion = null;
    private array $newsCache;

    public function __construct(ManagerFactory $managerFactory, private PermissionManager $permissionManager, private UserManager $userManager)
    {
        parent::__construct($managerFactory);
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
            assert($this->discussion instanceof Discussion);

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

        $discussion->setNumberOfPosts($row->related(Post::TABLE)->count("id"));
        $discussion->setWebName(Strings::webalize($discussion->getId() . "-" . $discussion->getCaption()));

        return $discussion;
    }

    protected function metaMap(BaseModel &$model, $userId = null): void
    {
        assert($model instanceof Discussion);
        $model->setCanRead(empty($model->getReadRightName()) || $this->user->isAllowed($this->user->getId(), Privilege::USR($model->getReadRightName())));
        $model->setCanWrite(empty($model->getWriteRightName()) || $this->user->isAllowed($this->user->getId(), Privilege::USR($model->getWriteRightName())));
        $model->setCanDelete($this->userManager->isAdmin() || (!empty($model->getDeleteRightName()) && $this->user->isAllowed($this->user->getId(), Privilege::USR($model->getDeleteRightName()))));
        $model->setCanStick($this->userManager->isAdmin() || (!empty($model->getStickyRightName()) && $this->user->isAllowed($this->user->getId(), Privilege::USR($model->getStickyRightName()))));

        $model->setNewInfo($this->getNewInfo($model->getId()));
    }

    /**
     * Load user-related informations regarding this discussion
     * @param int $discussionId
     * @return NewInfo
     */
    private function getNewInfo(int $discussionId): NewInfo
    {
        if (!isset($this->newsCache)) {
            $this->newsCache = $this->database->query("
            SELECT 
            `" . Discussion::TABLE . "`.`id`, 
            `" . Post::TABLE_READ . "`.`last_date` AS `lastVisit`, 
            
            (
                SELECT COUNT(`" . Post::TABLE . "`.`id`) 
                FROM `" . Post::TABLE . "` 
                WHERE `" . Post::TABLE . "`.`insert_date` > `" . Post::TABLE_READ . "`.`last_date` AND `" . Post::TABLE . "`.`discussion_id` = " . Discussion::TABLE . ".`id`
            ) AS `newInfo`, 
            
            (
                SELECT COUNT(`" . Post::TABLE . "`.`id`) 
                FROM `" . Post::TABLE . "` 
                WHERE `" . Post::TABLE . "`.`discussion_id` = " . Discussion::TABLE . ".`id`
            ) AS `numberOfPosts` 
            FROM " . Discussion::TABLE . " 
            LEFT JOIN `" . Post::TABLE_READ . "` ON " . Discussion::TABLE . ".`id` = `" . Post::TABLE_READ . "`.`discussion_id` AND (`" . Post::TABLE_READ . "`.`user_id` = ?) 
            WHERE " . Discussion::TABLE . ".`id` = ? ORDER BY " . Discussion::TABLE . ".`order_flag` ASC", $this->user->getId(), $discussionId)->fetchPairs("id");
        }

        $niData = $this->newsCache[$discussionId];

        return new NewInfo($discussionId, $niData["newInfo"], $niData["lastVisit"]);
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
     * @return Discussion[]
     */
    public function getListUserAllowed(int $userId): array
    {
        $selector = $this->database->table($this->getTable())->order("order_flag ASC");

        $readPerms = $this->permissionManager->getUserAllowedPermissionNames($this->userManager->getById($this->user->getId()), Permission::TYPE_USER);

        if (!empty($readPerms)) {
            $selector->where("read_rights IS NULL OR read_rights IN (?)", $readPerms);
        } else {
            $selector->where("read_rights IS NULL");
        }

        return $this->mapAll($selector->fetchAll());
    }

    /**
     * @return BaseModel[]
     */
    public function getList(?array $idList = null, string $idField = "id", ?int $limit = null, ?int $offset = null, ?string $order = null): array
    {
        return $this->mapAll($this->database->table($this->getTable())->order("order_flag ASC")->fetchAll());
    }

    /**
     * Get array of discussion ids which user is allowed to read
     * @return int[]
     */
    public function getIdsUserAllowed(int $userId): array
    {
        $readPerms = $this->permissionManager->getUserAllowedPermissionNames($this->userManager->getById($userId), Permission::TYPE_USER);
        return $this->database->table($this->getTable())->where("read_rights IS NULL OR read_rights = '' OR read_rights IN (?)", $readPerms)->fetchPairs(null, "id");
    }

    protected function getClassName(): string
    {
        return Discussion::class;
    }

    /**
     * @return Field[]
     */
    protected function getScheme(): array
    {
        return DiscussionMapper::scheme();
    }

    /**
     * Check edit permission
     * @param Discussion $entity
     */
    public function canEdit($entity, int $userId): bool
    {
        return in_array($userId, $this->userManager->getUserIdsWithPrivilege(Privilege::SYS("DSSETUP")));
    }

    /**
     * Check read permission
     * @param Discussion $entity
     */
    public function canRead($entity, int $userId): bool
    {
        return in_array($entity->getId(), $this->getIdsUserAllowed($userId));
    }

    /**
     * Get user ids allowed to read given discussion
     * @param Discussion $record
     * @return int[]|mixed[]
     */
    public function getAllowedReaders(BaseModel $record): array
    {
        assert($record instanceof Discussion);
        if (empty($record->getReadRightName())) {
            return $this->getAllUserIds();
        }

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
