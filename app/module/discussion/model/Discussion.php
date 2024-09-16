<?php

namespace Tymy\Module\Discussion\Model;

use Nette\Utils\DateTime;
use Tymy\Module\Core\Model\BaseModel;
use Tymy\Module\Discussion\Mapper\DiscussionMapper;

/**
 * Description of Discussion
 */
class Discussion extends BaseModel
{
    public const TABLE = "discussion";
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
    private bool $editablePosts = false;
    private ?int $order = null;
    private bool $canRead = false;
    private bool $canWrite = false;
    private bool $canDelete = false;
    private bool $canStick = false;
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
        return isset($this->newInfo) ? $this->newInfo->getNewsCount() : 0;
    }

    public function getWebName(): string
    {
        return $this->webName;
    }

    public function setCreatedById(?int $createdById): static
    {
        $this->createdById = $createdById;
        return $this;
    }

    public function setUpdatedById(?int $updatedById): static
    {
        $this->updatedById = $updatedById;
        return $this;
    }

    public function setCreatedAt(?DateTime $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function setUpdatedAt(?DateTime $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function setCaption(string $caption): static
    {
        $this->caption = $caption;
        return $this;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function setReadRightName(?string $readRightName): static
    {
        $this->readRightName = $readRightName;
        return $this;
    }

    public function setWriteRightName(?string $writeRightName): static
    {
        $this->writeRightName = $writeRightName;
        return $this;
    }

    public function setDeleteRightName(?string $deleteRightName): static
    {
        $this->deleteRightName = $deleteRightName;
        return $this;
    }

    public function setStickyRightName(?string $stickyRightName): static
    {
        $this->stickyRightName = $stickyRightName;
        return $this;
    }

    public function setPublicRead($publicRead): static
    {
        $this->publicRead = (bool) $publicRead;
        return $this;
    }

    public function setEditablePosts($editablePosts): static
    {
        $this->editablePosts = (bool) $editablePosts;
        return $this;
    }

    public function setOrder(?int $order): static
    {
        $this->order = $order;
        return $this;
    }

    public function setCanRead(bool $canRead): static
    {
        $this->canRead = $canRead;
        return $this;
    }

    public function setCanWrite(bool $canWrite): static
    {
        $this->canWrite = $canWrite;
        return $this;
    }

    public function setCanDelete(bool $canDelete): static
    {
        $this->canDelete = $canDelete;
        return $this;
    }

    public function setCanStick(bool $canStick): static
    {
        $this->canStick = $canStick;
        return $this;
    }

    public function setNewPosts(int $newPosts): static
    {
        return $this;
    }

    public function setNumberOfPosts(int $numberOfPosts): static
    {
        $this->numberOfPosts = $numberOfPosts;
        return $this;
    }

    public function getNewInfo(): NewInfo
    {
        return $this->newInfo;
    }

    public function setNewInfo(NewInfo $newInfo): static
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

    /**
     * @return \Tymy\Module\Core\Model\Field[]
     */
    public function getScheme(): array
    {
        return DiscussionMapper::scheme();
    }

    public function getTable(): string
    {
        return self::TABLE;
    }

    /**
     * @return mixed[]
     */
    public function jsonSerialize(): array
    {
        return parent::jsonSerialize() +
                [
                    "status" => "ACTIVE",   /** @deprecated DELETED statuses discussions has been deleted */
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
