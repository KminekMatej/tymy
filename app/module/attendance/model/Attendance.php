<?php

namespace Tymy\Module\Attendance\Model;

use JsonSerializable;
use Nette\Utils\DateTime;
use Tymy\Module\Attendance\Mapper\AttendanceMapper;
use Tymy\Module\Core\Model\BaseModel;
use Tymy\Module\User\Model\SimpleUser;

/**
 * Description of Attendance
 */
class Attendance extends BaseModel implements JsonSerializable
{
    public const TABLE = "attendance";
    public const MODULE = "attendance";

    private int $userId;
    private int $eventId;
    private ?int $preStatusId = null;
    private ?string $preStatus = null;
    private ?string $preDescription = null;
    private ?int $preUserMod = null;
    private ?DateTime $preDatMod = null;
    private ?int $postStatusId = null;
    private ?string $postStatus = null;
    private ?string $postDescription = null;
    private ?int $postUserMod = null;
    private ?DateTime $postDatMod = null;
    private ?SimpleUser $user = null;

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getEventId(): int
    {
        return $this->eventId;
    }

    public function getPreStatusId(): ?int
    {
        return $this->preStatusId;
    }

    public function getPreStatus(): ?string
    {
        return $this->preStatus;
    }

    public function getPreDescription(): ?string
    {
        return $this->preDescription;
    }

    public function getPreUserMod(): ?int
    {
        return $this->preUserMod;
    }

    public function getPreDatMod(): ?DateTime
    {
        return $this->preDatMod;
    }

    public function getPostStatusId(): ?int
    {
        return $this->postStatusId;
    }

    public function getPostStatus(): ?string
    {
        return $this->postStatus;
    }

    public function getPostDescription(): ?string
    {
        return $this->postDescription;
    }

    public function getPostUserMod(): ?int
    {
        return $this->postUserMod;
    }

    public function getPostDatMod(): ?DateTime
    {
        return $this->postDatMod;
    }

    public function getUser(): ?SimpleUser
    {
        return $this->user;
    }

    public function setUserId(int $userId): static
    {
        $this->userId = $userId;
        return $this;
    }

    public function setEventId(int $eventId): static
    {
        $this->eventId = $eventId;
        return $this;
    }

    public function setPreStatusId(?int $preStatusId): static
    {
        $this->preStatusId = $preStatusId;
        return $this;
    }

    public function setPreStatus(?string $preStatus): static
    {
        $this->preStatus = $preStatus;
        return $this;
    }

    public function setPreDescription(?string $preDescription): static
    {
        $this->preDescription = $preDescription;
        return $this;
    }

    public function setPreUserMod(?int $preUserMod): static
    {
        $this->preUserMod = $preUserMod;
        return $this;
    }

    public function setPreDatMod(?DateTime $preDatMod): static
    {
        $this->preDatMod = $preDatMod;
        return $this;
    }

    public function setPostStatusId(?int $postStatusId): static
    {
        $this->postStatusId = $postStatusId;
        return $this;
    }

    public function setPostStatus(?string $postStatus): static
    {
        $this->postStatus = $postStatus;
        return $this;
    }

    public function setPostDescription(?string $postDescription): static
    {
        $this->postDescription = $postDescription;
        return $this;
    }

    public function setPostUserMod(?int $postUserMod): static
    {
        $this->postUserMod = $postUserMod;
        return $this;
    }

    public function setPostDatMod(?DateTime $postDatMod): static
    {
        $this->postDatMod = $postDatMod;
        return $this;
    }

    public function setUser(?SimpleUser $user = null): static
    {
        $this->user = $user;
        return $this;
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
        return AttendanceMapper::scheme();
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
        return parent::jsonSerialize() + [
            "user" => $this->user !== null ? $this->user->jsonSerialize() : null,
            "preStatus" => $this->preStatus,
            "postStatus" => $this->postStatus,
        ];
    }
}
