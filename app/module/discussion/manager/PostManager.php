<?php

namespace Tymy\Module\Discussion\Manager;

use Nette\Database\Explorer;
use Nette\Database\IRow;
use Nette\Utils\DateTime;
use Tymy\Module\Core\Factory\ManagerFactory;
use Tymy\Module\Core\Manager\BaseManager;
use Tymy\Module\Core\Model\BaseModel;
use Tymy\Module\Core\Service\BbService;
use Tymy\Module\Discussion\Mapper\PostMapper;
use Tymy\Module\Discussion\Model\Discussion;
use Tymy\Module\Discussion\Model\DiscussionPosts;
use Tymy\Module\Discussion\Model\Post;
use Tymy\Module\PushNotification\Manager\NotificationGenerator;
use Tymy\Module\PushNotification\Manager\PushNotificationManager;
use Tymy\Module\User\Manager\UserManager;

/**
 * Description of PostManager
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 17. 9. 2020
 */
class PostManager extends BaseManager
{
    public const POSTS_PER_PAGE = 20;
    // this number used to be 1 be now it contains reserve for sticky posts, which are included
    // so if discussion has 6 sticky posts, even one new is not displayed at first page
    public const MINIMUM_NUMBER_OF_POSTS_DISPLAYED_WITH_NEW_POSTS = 5;
    public const MAXIMUM_FIRST_PAGE_SIZE = 200;

    private DiscussionManager $discussionManager;
    private PushNotificationManager $pushNotificationManager;
    private NotificationGenerator $notificationGenerator;
    private UserManager $userManager;
    private bool $inBbCode = true;
    private ?Discussion $discussion;
    private ?Post $post;

    public function __construct(ManagerFactory $managerFactory, DiscussionManager $discussionManager, UserManager $userManager, PushNotificationManager $pushNotificationManager, NotificationGenerator $notificationGenerator)
    {
        parent::__construct($managerFactory);
        $this->discussionManager = $discussionManager;
        $this->userManager = $userManager;
        $this->pushNotificationManager = $pushNotificationManager;
        $this->notificationGenerator = $notificationGenerator;
    }

    public function allowDiscussion($discussionId)
    {
        $this->discussion = $this->loadRecord($discussionId, $this->discussionManager);

        if (!$this->discussion->getCanRead()) {
            $this->responder->E4001_VIEW_NOT_PERMITTED(Discussion::MODULE, $discussionId);
        }
    }

    protected function allowCreate(?array &$data = null): void
    {
        if (!$this->discussion->getCanWrite()) {
            $this->responder->E4003_CREATE_NOT_PERMITTED("Post");
        }
    }

    protected function allowDelete(?int $recordId): void
    {
        $this->allowUpdate($recordId);
        
        if ($this->discussion->getCanDelete()) {
            return; //this user is specifically privileged to delete in this discussion
        }
        
        if ($this->discussion->getEditablePosts()) {
            $this->respondForbidden();
        }
        
        //posts in this discussions are editable, so delete is approved for the post author
        if ($this->post->getCreatedById() !== $this->user->getId()) {
            $this->respondForbidden();
        }
    }

    protected function allowRead(?int $recordId = null): void
    {
        $this->post = $this->loadRecord($recordId);
        if ($this->post->getDiscussionId() !== $this->discussion->getId()) {
            $this->respondNotFound();
        }
        if (!$this->canRead($this->post, $this->user->getId())) {
            $this->respondForbidden();
        }
    }

    protected function allowUpdate(?int $recordId = null, ?array &$data = null): void
    {
        $this->post = $this->loadRecord($recordId);

        if (!$this->post) {
            $this->respondNotFound();
        }

        if ($this->post->getDiscussionId() !== $this->discussion->getId()) {
            $this->respondNotFound();
        }

        if (!empty($data) && array_key_exists("post", $data) && !$this->canEdit($this->post, $this->user->getId())) {    //only the creator can update his post
            $this->respondForbidden();
        }

        if (!empty($data) && array_key_exists("sticky", $data) && $data["sticky"] !== $this->post->getSticky() && !$this->discussion->getCanStick()) { //check stick rights
            $this->respondForbidden();
        }
    }

    public function map(?IRow $row, bool $force = false): ?BaseModel
    {
        if (!$row) {
            return null;
        }

        $post = parent::map($row, $force);

        /* @var $post Post */
        if (!$this->inBbCode) {
            $post->setPost(BbService::bb2Html($post->getPost()));
        }

        if (property_exists($post, "newPost")) {
            $post->setNewPost($row->newPost);
        }

        $post->setCreatedAtStr($post->getCreatedAt()->format(BaseModel::DATETIME_CZECH_NO_SECS_FORMAT));
        $post->setCreatedBy($this->userManager->getSimpleUser($post->getCreatedById()));
        if ($post->getUpdatedAt()) {
            $post->setUpdatedAtStr($post->getUpdatedAt()->format(BaseModel::DATETIME_CZECH_NO_SECS_FORMAT));
        }

        return $post;
    }

    public function create(array $data, ?int $resourceId = null): Post
    {
        $data["discussionId"] = $resourceId;
        $data["createdAt"] = new DateTime();

        $this->allowDiscussion($resourceId);
        $this->allowCreate($data);

        $createdRow = parent::createByArray($data);
        $createdPost = $this->getById($createdRow->id);


        $notification = $this->notificationGenerator->createPost($this->discussion, $createdPost);
        $this->pushNotificationManager->notifyUsers($notification, $this->discussionManager->getAllowedReaders($this->discussion));

        return $createdPost;
    }

    public function read(int $resourceId, ?int $subResourceId = null): Post
    {
        $this->allowDiscussion($resourceId);
        $this->allowRead($subResourceId);

        return $this->getById($subResourceId);
    }

    /**
     * Update POST with checking permissions
     * 
     * @param array $data
     * @param int $resourceId Id of discussion
     * @param int|null $subResourceId Id of post
     * @return Post
     */
    public function update(array $data, int $resourceId, ?int $subResourceId = null): Post
    {
        unset($data["discussionId"]);   //do not update discussionId
        $this->allowDiscussion($resourceId);
        $this->allowUpdate($subResourceId, $data);

        $data["updatedAt"] = new DateTime();
        $data["updatedById"] = $this->user->getId();

        parent::updateByArray($subResourceId, $data);

        return $this->getById($subResourceId);
    }

    public function delete(int $resourceId, ?int $subResourceId = null): int
    {
        $this->allowDiscussion($resourceId);
        $this->allowDelete($subResourceId);

        return parent::deleteRecord($subResourceId);
    }

    public function mode(int $discussionId, int $page = 1, string $mode = "html", ?string $search = null, ?string $suser = null, ?string $jump2Date = null): DiscussionPosts
    {
        $this->allowDiscussion($discussionId);

        if ($jump2Date) {
            $page = $this->getPageNumberFromDate($discussionId, $this->discussion->getNewInfo()->getNewsCount(), new DateTime($jump2Date));
        }

        $posts = $this->getPostsFromDiscussion($this->discussion->getId(), $page, $mode == "bb", $search, intval($suser));
        return new DiscussionPosts($this->discussion, $page, $this->getNumberOfPages($this->discussion->getId()), $posts);
    }

    public function getById(int $id, bool $force = false): ?BaseModel
    {
        return $this->map($this->database->query("
            SELECT `ds_items`.*, IF(`ds_read`.`last_date`<`ds_items`.`insert_date`,1,0) AS 'newPost' 
            FROM `ds_items` 
            LEFT JOIN `ds_read` ON `ds_read`.`ds_id`=`ds_items`.`ds_id` AND `ds_read`.`user_id`=? 
            WHERE (`ds_items`.`id` = ?)
            ORDER BY `ds_items`.`sticky` DESC, `ds_items`.`insert_date` 
            DESC LIMIT " . self::POSTS_PER_PAGE, $this->user->getId(), $id)->fetch());
    }

    protected function getClassName(): string
    {
        return Post::class;
    }

    protected function getScheme(): array
    {
        return PostMapper::scheme();
    }

    /**
     * Check edit permissions
     * @param Post $entity
     * @param int $userId
     * @return bool
     */
    public function canEdit($entity, $userId): bool
    {
        return $entity->getCreatedById() == $userId;
    }

    /**
     * Check read permissions
     * @param Post $entity
     * @param int $userId
     * @return bool
     */
    public function canRead($entity, $userId): bool
    {
        return in_array($entity->getDiscussionId(), $this->discussionManager->getIdsUserAllowed($userId));
    }

    /**
     *
     * @param Post $record
     * @return int[]
     */
    public function getAllowedReaders(BaseModel $record): array
    {
        /* @var $record Post */
        return $this->discussionManager->getAllowedReadersById($record->getDiscussionId());
    }

    /**
     * Get posts from discussion, selected by page, optionally filtered with search string and/or search user id
     * @param int $discussionId
     * @param int $page
     * @param string|null $search
     * @param int|null $searchUserId
     * @return Post[]|null
     */
    private function getPostsFromDiscussion(int $discussionId, int $page = 1, $inBBCode = true, ?string $search = null, ?int $searchUserId = null): ?array
    {
        $this->allowDiscussion($discussionId);

        $this->inBbCode = $inBBCode;
        $offset = ($page - 1) * self::POSTS_PER_PAGE;

        $query = [];
        $params = [];
        $query[] = "SELECT `ds_items`.*, IF(`ds_read`.`last_date`<`ds_items`.`insert_date`,1,0) AS 'newPost'";
        $query[] = "FROM `ds_items`";
        $query[] = "LEFT JOIN `ds_read` ON `ds_read`.`ds_id`=`ds_items`.`ds_id` AND `ds_read`.`user_id`=?";
        $params[] = $this->user->getId();
        $query[] = "WHERE (`ds_items`.`ds_id` = ?)";
        $params[] = $discussionId;
        if (!empty($search)) {
            $query[] = "AND `item` LIKE ?";
            $params[] = "%$search%";
        }
        if (!empty($searchUserId)) {
            $query[] = "AND `ds_read`.`user_id` = ?";
            $params[] = $searchUserId;
        }

        $query[] = "ORDER BY `ds_items`.`sticky` DESC, `ds_items`.`insert_date` DESC";
        $query[] = "LIMIT ?";
        $params[] = $page == 1 ? $this->getFirstPageSize() : self::POSTS_PER_PAGE;
        $query[] = "OFFSET ?";
        $params[] = $offset;

        $this->markAllAsRead($this->user->getId(), $discussionId);

        return $this->mapAll($this->database->query(join(" ", $query), ...$params)->fetchAll());
    }

    /**
     * Return size of first page - usually its twenty, but with a lot of new posts it can get higher, up until 200
     *
     * @param int|null $newPosts
     * @return int
     */
    private function getFirstPageSize(?int $newPosts = null): int
    {
        $size = ($newPosts == null ? 0 : $newPosts) + self::MINIMUM_NUMBER_OF_POSTS_DISPLAYED_WITH_NEW_POSTS;
        $size = max($size, self::POSTS_PER_PAGE);
        return min($size, self::MAXIMUM_FIRST_PAGE_SIZE);
    }

    /**
     * Get proper page number when searching for page of specific date
     *
     * @param int $dicussionId
     * @param int $newPosts
     * @param DateTime $jumpDate
     * @return int
     */
    private function getPageNumberFromDate(int $dicussionId, int $newPosts, DateTime $jumpDate): int
    {
        $firstPageSize = $this->getFirstPageSize($newPosts);
        $postCountBeforeDate = $this->database->table($this->getTable())->where("ds_id", $dicussionId)->where("insert_date > ?", $jumpDate)->count("id");
        if ($postCountBeforeDate <= 0) {
            return 1;
        }
        return max(ceil((($postCountBeforeDate - $firstPageSize) / self::POSTS_PER_PAGE) + 1), 1);
    }

    /**
     * Return number of all posts, optionally filtered with search string and/or search user id
     * @param int $discussionId
     * @param string|null $search
     * @param int|null $searchUserId
     * @return int
     */
    public function countPosts(int $discussionId, ?string $search = null, ?int $searchUserId = null)
    {
        $selector = $this->database->table($this->getTable())
                ->where("ds_id", $discussionId);

        if (!empty($search)) {
            $selector->where("item LIKE ?", "%$search%");
        }

        if (!empty($searchUserId)) {
            $selector->where("user_id", $searchUserId);
        }

        return $selector->count("id");
    }

    /**
     * Get number of all pages in selected discussion, optionally filtered with search string and/or search user id
     *
     * @param int $discussionId
     * @param string|null $search
     * @param int|null $searchUserId
     * @return int
     */
    public function getNumberOfPages(int $discussionId, ?string $search = null, ?int $searchUserId = null): int
    {
        $count = $this->countPosts($discussionId, $search, $searchUserId);
        return (int) ($count / self::POSTS_PER_PAGE) + 1;
    }

    /**
     * Mark all items in discussion as read for user
     * 
     * @param int $userId
     * @param int $discussionId
     * @return void
     */
    private function markAllAsRead(int $userId, int $discussionId): void
    {
        $updated = $this->database->table(Post::TABLE_READ)
                ->where("ds_id", $discussionId)
                ->where("user_id", $userId)
                ->update([
            "last_date" => Explorer::literal("NOW()")
        ]);

        if (!$updated) { //record does not exist yet, create new one for user and discussion
            $this->database->table(Post::TABLE_READ)
                    ->insert([
                        "last_date" => Explorer::literal("NOW()"),
                        "ds_id" => $discussionId,
                        "user_id" => $userId,
            ]);
        }
    }

    /**
     * Stick/unstick a post
     * 
     * @param int $postId
     * @param int $discussionId
     * @param bool $stick
     * @return void
     */
    public function stickPost(int $postId, int $discussionId, bool $stick = true): void
    {
        $this->update([
            "sticky" => $stick
                ], $discussionId, $postId);
    }
}