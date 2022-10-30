<?php

namespace Tymy\Module\Discussion\Manager;

use Exception;
use Nette\Database\Explorer;
use Nette\Database\IRow;
use Nette\Database\Table\ActiveRow;
use Nette\Utils\DateTime;
use Tymy\Module\Core\Factory\ManagerFactory;
use Tymy\Module\Core\Manager\BaseManager;
use Tymy\Module\Core\Model\BaseModel;
use Tymy\Module\Core\Model\Field;
use Tymy\Module\Core\Service\BbService;
use Tymy\Module\Discussion\Mapper\PostMapper;
use Tymy\Module\Discussion\Model\Discussion;
use Tymy\Module\Discussion\Model\DiscussionPosts;
use Tymy\Module\Discussion\Model\Post;
use Tymy\Module\PushNotification\Manager\NotificationGenerator;
use Tymy\Module\PushNotification\Manager\PushNotificationManager;
use Tymy\Module\User\Manager\UserManager;
use Tymy\Module\User\Model\User;
use function mb_strlen;

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
    private bool $inBbCode = true;
    private ?Discussion $discussion = null;
    private ?Post $post = null;
    private array $reactionsCache;

    public function __construct(ManagerFactory $managerFactory, private DiscussionManager $discussionManager, private UserManager $userManager, private PushNotificationManager $pushNotificationManager, private NotificationGenerator $notificationGenerator)
    {
        parent::__construct($managerFactory);
    }

    public function allowDiscussion($discussionId): void
    {
        $this->discussion = $this->loadRecord($discussionId, $this->discussionManager);
        assert($this->discussion instanceof Discussion);

        if (!$this->discussion) {
            $this->respondNotFound(Discussion::MODULE, $discussionId);
        }

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
        if (!$recordId) {
            $this->respondBadRequest();
        }

        $this->post = $this->loadRecord($recordId);
        assert($this->post instanceof Post);
        if ($this->post->getDiscussionId() !== $this->discussion->getId()) {
            $this->respondNotFound();
        }
        if (!$this->canRead($this->post, $this->user->getId())) {
            $this->respondForbidden();
        }
    }

    protected function allowUpdate(?int $recordId = null, ?array &$data = null): void
    {
        if (!$recordId) {
            $this->respondBadRequest();
        }

        $this->post = $this->loadRecord($recordId);

        if (!$this->post) {
            $this->respondNotFound();
        }
        assert($this->post instanceof Post);

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
        if ($row === null) {
            return null;
        }
        assert($row instanceof ActiveRow);

        $post = parent::map($row, $force);

        assert($post instanceof Post);
        if (!$this->inBbCode) {
            $post->setPost(BbService::bb2Html($post->getPost()));
        }

        if (property_exists($row, "newPost")) {
            $post->setNewPost($row->newPost);
        }

        $post->setCreatedAtStr($post->getCreatedAt()->format(BaseModel::DATETIME_CZECH_NO_SECS_FORMAT));
        if ($post->getCreatedById()) {
            $post->setCreatedBy($this->userManager->getSimpleUser($post->getCreatedById()));
        }
        if ($post->getUpdatedAt()) {
            $post->setUpdatedAtStr($post->getUpdatedAt()->format(BaseModel::DATETIME_CZECH_NO_SECS_FORMAT));
        }

        $post->setReactions($this->getReactions($post->getId()));

        return $post;
    }

    /**
     * Load aray of reactions to a certain posts.
     * @return array in the form of ["utf8mb4smiley" => [1,2,4]] .. where 1,2,4 are user ids, reacting with this smile
     */
    private function getReactions(int $postId): array
    {
        if (!isset($this->reactionsCache)) {
            $this->reactionsCache = $this->database->table(Post::TABLE_REACTION)
                ->select("id")
                ->select("discussion_post_id")
                ->select("GROUP_CONCAT(user_id,'|',reaction) AS reactions")
                ->group("discussion_post_id")
                ->fetchPairs("discussion_post_id");
        }

        if (!array_key_exists($postId, $this->reactionsCache)) {
            return [];
        }

        $postReactions = [];
        foreach (explode(",", $this->reactionsCache[$postId]["reactions"]) as $reactionString) {
            if (empty(trim($reactionString))) {
                continue;
            }
            $reactionData = explode("|", $reactionString);
            $userId = $reactionData[0];
            $reaction = $reactionData[1];

            if (!isset($postReactions[$reaction])) {
                $postReactions[$reaction] = [];
            }

            $postReactions[$reaction][] = (int) $userId;
        }

        return $postReactions;
    }

    public function create(array $data, ?int $resourceId = null): Post
    {
        $data["discussionId"] = $resourceId;
        $data["createdAt"] = new DateTime();
        $tymyUser = $this->userManager->getById($this->user->getId());
        assert($tymyUser instanceof User);
        $data["userName"] = $tymyUser->getDisplayName();

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
     * @param int $resourceId Id of discussion
     * @param int|null $subResourceId Id of post
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
            try {
                $page = $this->getPageNumberFromDate($discussionId, $this->discussion->getNewInfo()->getNewsCount(), new DateTime($jump2Date)); //sanitize invalid inputs
            } catch (Exception) {
                $page = 1;
            }
        }

        $posts = $this->getPostsFromDiscussion($this->discussion->getId(), $page, $mode == "bb", $search, (int) $suser);
        return new DiscussionPosts($this->discussion, $page, $this->getNumberOfPages($this->discussion->getId()), $posts);
    }

    public function getById(int $id, bool $force = false): ?BaseModel
    {
        return $this->map($this->database->query("
            SELECT `discussion_post`.*, IF(`discussion_read`.`last_date`<`discussion_post`.`insert_date`,1,0) AS 'newPost' 
            FROM `discussion_post` 
            LEFT JOIN `discussion_read` ON `discussion_read`.`discussion_id`=`discussion_post`.`discussion_id` AND `discussion_read`.`user_id`=? 
            WHERE (`discussion_post`.`id` = ?)
            ORDER BY `discussion_post`.`sticky` DESC, `discussion_post`.`insert_date` 
            DESC LIMIT " . self::POSTS_PER_PAGE, $this->user->getId(), $id)->fetch());
    }

    protected function getClassName(): string
    {
        return Post::class;
    }

    /**
     * @return Field[]
     */
    protected function getScheme(): array
    {
        return PostMapper::scheme();
    }

    /**
     * Check edit permissions
     * @param Post $entity
     */
    public function canEdit($entity, int $userId): bool
    {
        return $entity->getCreatedById() == $userId;
    }

    /**
     * Check read permissions
     * @param Post $entity
     */
    public function canRead($entity, int $userId): bool
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
        assert($record instanceof Post);
        return $this->discussionManager->getAllowedReadersById($record->getDiscussionId());
    }

    /**
     * Get posts from discussion, selected by page, optionally filtered with search string and/or search user id
     * @return BaseModel[]
     */
    private function getPostsFromDiscussion(int $discussionId, int $page = 1, bool $inBBCode = true, ?string $search = null, ?int $searchUserId = null): array
    {
        $this->inBbCode = $inBBCode;
        $offset = ($page - 1) * self::POSTS_PER_PAGE;

        $query = [];
        $params = [];
        $query[] = "SELECT `discussion_post`.*, IF(`discussion_read`.`last_date`<`discussion_post`.`insert_date`,1,0) AS 'newPost'";
        $query[] = "FROM `discussion_post`";
        $query[] = "LEFT JOIN `discussion_read` ON `discussion_read`.`discussion_id`=`discussion_post`.`discussion_id` AND `discussion_read`.`user_id`=?";
        $params[] = $this->user->getId();
        $query[] = "WHERE (`discussion_post`.`discussion_id` = ?)";
        $params[] = $discussionId;
        if (!empty($search)) {
            $query[] = "AND `item` LIKE ?";
            $params[] = "%$search%";
        }
        if ($searchUserId) {
            $query[] = "AND `discussion_post`.`user_id` = ?";
            $params[] = $searchUserId;
        }

        $query[] = "ORDER BY `discussion_post`.`sticky` DESC, `discussion_post`.`insert_date` DESC";
        $query[] = "LIMIT ?";
        $params[] = $page == 1 ? $this->getFirstPageSize() : self::POSTS_PER_PAGE;
        $query[] = "OFFSET ?";
        $params[] = $offset;

        $posts = $this->mapAll($this->database->query(implode(" ", $query), ...$params)->fetchAll());

        if (!$this->user->getIdentity()->ghost) { /* @phpstan-ignore-line */
            $this->markAllAsRead($this->user->getId(), $discussionId);
        }

        return $posts;
    }

    /**
     * Return size of first page - usually its twenty, but with a lot of new posts it can get higher, up until 200
     */
    private function getFirstPageSize(?int $newPosts = null): int
    {
        $size = ($newPosts == null ? 0 : $newPosts) + self::MINIMUM_NUMBER_OF_POSTS_DISPLAYED_WITH_NEW_POSTS;
        $size = max($size, self::POSTS_PER_PAGE);
        return min($size, self::MAXIMUM_FIRST_PAGE_SIZE);
    }

    /**
     * Get proper page number when searching for page of specific date
     */
    private function getPageNumberFromDate(int $dicussionId, int $newPosts, DateTime $jumpDate): int|float
    {
        $firstPageSize = $this->getFirstPageSize($newPosts);
        $postCountBeforeDate = $this->database->table($this->getTable())->where("discussion_id", $dicussionId)->where("insert_date > ?", $jumpDate)->count("id");
        if ($postCountBeforeDate <= 0) {
            return 1;
        }
        return max(ceil((($postCountBeforeDate - $firstPageSize) / self::POSTS_PER_PAGE) + 1), 1);
    }

    /**
     * Return number of all posts, optionally filtered with search string and/or search user id
     */
    public function countPosts(int $discussionId, ?string $search = null, ?int $searchUserId = null): int
    {
        $selector = $this->database->table($this->getTable())
                ->where("discussion_id", $discussionId);

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
     */
    public function getNumberOfPages(int $discussionId, ?string $search = null, ?int $searchUserId = null): int
    {
        $count = $this->countPosts($discussionId, $search, $searchUserId);
        return (int) ($count / self::POSTS_PER_PAGE) + 1;
    }

    /**
     * Mark all items in discussion as read for user
     */
    private function markAllAsRead(int $userId, int $discussionId): void
    {
        $selector = $this->database->table(Post::TABLE_READ)
            ->where("discussion_id", $discussionId)
            ->where("user_id", $userId);

        if ($selector->count() !== 0) {
            $selector->update([
                "last_date" => Explorer::literal("NOW()")
            ]);
        } else {//record does not exist yet, create new one for user and discussion
            $this->database->table(Post::TABLE_READ)
                ->insert([
                    "last_date" => Explorer::literal("NOW()"),
                    "discussion_id" => $discussionId,
                    "user_id" => $userId,
            ]);
        }
    }

    /**
     * Stick/unstick a post
     */
    public function stickPost(int $postId, int $discussionId, bool $stick = true): void
    {
        $updates = [
            "sticky" => $stick
        ];
        $this->allowDiscussion($discussionId);
        $this->allowUpdate($postId, $updates);

        $this->update($updates, $discussionId, $postId);
    }

    /**
     * Create new reaction or delete existing one to a discussion post or update existing reaction
     */
    public function react(int $discussionId, int $postId, int $userId, string $reaction, bool $remove = false): void
    {
        $this->allowDiscussion($discussionId);
        $this->allowRead($postId);

        if ($remove) { //handle removal of this reaction
            $this->database->table(Post::TABLE_REACTION)
                ->where("user_id", $userId)
                ->where("discussion_post_id", $postId)
                ->where("reaction", $reaction)
                ->delete();

            return;
        }

        if (mb_strlen($reaction) !== 1) { //pass only emojis
            return;
        }

        //check if there is this reaction already
        $reactionsCnt = $this->database->table(Post::TABLE_REACTION)
            ->where("user_id", $userId)
            ->where("discussion_post_id", $postId)
            ->where("reaction", $reaction)
            ->count('id');

        if ($reactionsCnt !== 0) {
            //this reaction already exists - dont do anything
            return;
        }

        //create new reaction
        $this->database->table(Post::TABLE_REACTION)->insert([
            "user_id" => $userId,
            "discussion_post_id" => $postId,
            "reaction" => $reaction,
        ]);
    }
}
