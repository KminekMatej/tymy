<?php

namespace Tymy\Module\Discussion\Model;

use Nette\Utils\DateTime;
use Tymy\Module\Core\Model\BaseModel;
use Tymy\Module\Discussion\Mapper\DiscussionMapper;

/**
 * Description of Discussion
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 5. 6. 2020
 */
class Discussion extends BaseModel
{
    public const TABLE = "discussions";
    public const MODULE = "discussion";

    private ?int $createdById = null;
    private ?int $updatedById = null;
    private ?DateTime $createdAt = null;
    private ?DateTime $updatedAt = null;
    private string $caption;
    private ?string $description = null;
    private ?string $readRightName = null;
    private ?string $writeRightName = null;
    private ?string $deleteRightName = null;
    private ?string $stickyRightName = null;
    private bool $publicRead = false;
    private string $status;
    private bool $editablePosts = false;
    private ?int $order = null;
    private bool $canRead = false;
    private bool $canWrite = false;
    private bool $canDelete = false;
    private bool $canStick = false;
    private int $newPosts = 0;
    private int $numberOfPosts = 0;
    private NewInfo $newInfo;
    private string $webName;

    public function getCreatedById(): ?int
    {
        return $this->createdById;
    }

    public function getUpdatedById(): ?int
    {
        return $this->updatedById;
    }

    public function getCreatedAt(): ?DateTime
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?DateTime
    {
        return $this->updatedAt;
    }

    public function getCaption(): string
    {
        return $this->caption;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getReadRightName(): ?string
    {
        return $this->readRightName;
    }

    public function getWriteRightName(): ?string
    {
        return $this->writeRightName;
    }

    public function getDeleteRightName(): ?string
    {
        return $this->deleteRightName;
    }

    public function getStickyRightName(): ?string
    {
        return $this->stickyRightName;
    }

    public function getPublicRead(): bool
    {
        return $this->publicRead;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getEditablePosts(): bool
    {
        return $this->editablePosts;
    }

    public function getOrder(): ?int
    {
        return $this->order;
    }

    public function getCanRead(): bool
    {
        return $this->canRead;
    }

    public function getCanWrite(): bool
    {
        return $this->canWrite;
    }

    public function getCanDelete(): bool
    {
        return $this->canDelete;
    }

    public function getCanStick(): bool
    {
        return $this->canStick;
    }

    public function getNewPosts(): int
    {
        return $this->newInfo ? $this->newInfo->getNewsCount() : 0;
    }

    public function getWebName(): string
    {
        return $this->webName;
    }

    public function setCreatedById(?int $createdById)
    {
        $this->createdById = $createdById;
        return $this;
    }

    public function setUpdatedById(?int $updatedById)
    {
        $this->updatedById = $updatedById;
        return $this;
    }

    public function setCreatedAt(?DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function setUpdatedAt(?DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function setCaption(string $caption)
    {
        $this->caption = $caption;
        return $this;
    }

    public function setDescription(?string $description)
    {
        $this->description = $description;
        return $this;
    }

    public function setReadRightName(?string $readRightName)
    {
        $this->readRightName = $readRightName;
        return $this;
    }

    public function setWriteRightName(?string $writeRightName)
    {
        $this->writeRightName = $writeRightName;
        return $this;
    }

    public function setDeleteRightName(?string $deleteRightName)
    {
        $this->deleteRightName = $deleteRightName;
        return $this;
    }

    public function setStickyRightName(?string $stickyRightName)
    {
        $this->stickyRightName = $stickyRightName;
        return $this;
    }

    public function setPublicRead(string $publicRead)
    {
        $this->publicRead = $publicRead == "YES";
        return $this;
    }

    public function setStatus(string $status)
    {
        $this->status = $status;
        return $this;
    }

    public function setEditablePosts(string $editablePosts)
    {
        $this->editablePosts = $editablePosts == "YES";
        return $this;
    }

    public function setOrder(?int $order)
    {
        $this->order = $order;
        return $this;
    }

    public function setCanRead(bool $canRead)
    {
        $this->canRead = $canRead;
        return $this;
    }

    public function setCanWrite(bool $canWrite)
    {
        $this->canWrite = $canWrite;
        return $this;
    }

    public function setCanDelete(bool $canDelete)
    {
        $this->canDelete = $canDelete;
        return $this;
    }

    public function setCanStick(bool $canStick)
    {
        $this->canStick = $canStick;
        return $this;
    }

    public function setNewPosts(int $newPosts)
    {
        $this->newPosts = $newPosts;
        return $this;
    }

    public function setNumberOfPosts(int $numberOfPosts)
    {
        $this->numberOfPosts = $numberOfPosts;
        return $this;
    }

    public function getNewInfo(): NewInfo
    {
        return $this->newInfo;
    }

    public function setNewInfo(NewInfo $newInfo)
    {
        $this->newInfo = $newInfo;
        return $this;
    }

    public function setWebName(string $webName): void
    {
        $this->webName = $webName;
    }

    public function getModule(): string
    {
        return self::MODULE;
    }

    public function getScheme(): array
    {
        return DiscussionMapper::scheme();
    }

    public function getTable(): string
    {
        return self::TABLE;
    }

    public function jsonSerialize()
    {
        return parent::jsonSerialize() +
                [
                    "canRead" => $this->getCanRead(),
                    "canWrite" => $this->getCanWrite(),
                    "canDelete" => $this->getCanDelete(),
                    "canStick" => $this->getCanStick(),
                    "newPosts" => $this->getNewPosts(),
                    "numberOfPosts" => $this->numberOfPosts,
                    "newInfo" => $this->getNewInfo()->jsonSerialize(),
        ];
    }
}
