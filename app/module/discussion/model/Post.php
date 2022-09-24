<?php

namespace Tymy\Module\Discussion\Model;

use Nette\Utils\DateTime;
use Tymy\Module\Core\Model\BaseModel;
use Tymy\Module\Discussion\Mapper\PostMapper;
use Tymy\Module\User\Model\SimpleUser;

/**
 * Description of Post
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 14. 9. 2020
 */
class Post extends BaseModel
{
    public const TABLE = "discussion_post";
    public const TABLE_READ = "discussion_read";
    public const TABLE_REACTION = "discussion_post_reaction";
    public const MODULE = "discussion";

    private int $discussionId;
    private string $post;
    private ?int $createdById = null;
    private DateTime $createdAt;
    private ?DateTime $updatedAt = null;
    private ?int $updatedById = null;
    private bool $sticky = false;
    private bool $newPost = false;
    private array $reactions = [];
    private string $createdAtStr;
    private ?string $updatedAtStr = null;
    private ?SimpleUser $createdBy = null;

    public function getDiscussionId(): int
    {
        return $this->discussionId;
    }

    public function getPost(): string
    {
        return $this->post;
    }

    public function getCreatedById(): ?int
    {
        return $this->createdById;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?DateTime
    {
        return $this->updatedAt;
    }

    public function getUpdatedById(): ?int
    {
        return $this->updatedById;
    }

    public function getSticky(): bool
    {
        return $this->sticky;
    }

    public function getNewPost(): bool
    {
        return $this->newPost;
    }

    public function getReactions(): array
    {
        return $this->reactions;
    }

    public function getCreatedAtStr(): string
    {
        return $this->createdAtStr;
    }

    public function getUpdatedAtStr(): ?string
    {
        return $this->updatedAtStr;
    }

    public function getCreatedBy(): ?SimpleUser
    {
        return $this->createdBy;
    }

    public function setDiscussionId(int $discussionId)
    {
        $this->discussionId = $discussionId;
        return $this;
    }

    public function setPost(string $post)
    {
        $this->post = $post;
        return $this;
    }

    public function setCreatedById(?int $createdById)
    {
        $this->createdById = $createdById;
        return $this;
    }

    public function setCreatedAt(DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function setUpdatedAt(?DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function setUpdatedById(?int $updatedById)
    {
        $this->updatedById = $updatedById;
        return $this;
    }

    public function setSticky(bool $sticky)
    {
        $this->sticky = $sticky;
        return $this;
    }

    public function setNewPost(bool $newPost)
    {
        $this->newPost = $newPost;
        return $this;
    }

    public function setReactions(array $reactions)
    {
        $this->reactions = $reactions;
        return $this;
    }

    public function setCreatedAtStr(string $createdAtStr)
    {
        $this->createdAtStr = $createdAtStr;
        return $this;
    }

    public function setUpdatedAtStr(?string $updatedAtStr)
    {
        $this->updatedAtStr = $updatedAtStr;
        return $this;
    }

    public function setCreatedBy(?SimpleUser $createdBy)
    {
        $this->createdBy = $createdBy;
        return $this;
    }

    public function getModule(): string
    {
        return Discussion::MODULE;
    }

    public function getScheme(): array
    {
        return PostMapper::scheme();
    }

    public function getTable(): string
    {
        return Discussion::TABLE;
    }

    public function jsonSerialize()
    {
        return parent::jsonSerialize() + [
            "newPost" => $this->getNewPost(),
            "reactions" => $this->getReactions(),
            "createdAtStr" => $this->getCreatedAtStr(),
            "updatedAtStr" => $this->getUpdatedAtStr(),
            "createdBy" => $this->getCreatedBy() ? $this->getCreatedBy()->jsonSerialize() : null,
        ];
    }
}
