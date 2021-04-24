<?php

namespace Tymy\Module\Attendance\Model;

use JsonSerializable;
use Nette\Utils\DateTime;
use Tymy\Module\Attendance\Mapper\AttendanceMapper;
use Tymy\Module\Core\Model\BaseModel;
use Tymy\Module\User\Model\SimpleUser;

/**
 * Description of Attendance
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 21. 9. 2020
 */
class Attendance extends BaseModel implements JsonSerializable
{
    public const TABLE = "attendance";
    public const MODULE = "attendance";

    private int $userId;
    private int $eventId;
    private ?string $preStatus = null;
    private ?string $preDescription = null;
    private ?int $preUserMod = null;
    private ?DateTime $preDatMod = null;
    private ?string $postStatus = null;
    private ?string $postDescription = null;
    private ?int $postUserMod = null;
    private ?DateTime $postDatMod = null;
    private SimpleUser $user;

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getEventId(): int
    {
        return $this->eventId;
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

    public function getUser(): SimpleUser
    {
        return $this->user;
    }

    public function setUserId(int $userId)
    {
        $this->userId = $userId;
        return $this;
    }

    public function setEventId(int $eventId)
    {
        $this->eventId = $eventId;
        return $this;
    }

    public function setPreStatus(?string $preStatus)
    {
        $this->preStatus = $preStatus;
        return $this;
    }

    public function setPreDescription(?string $preDescription)
    {
        $this->preDescription = $preDescription;
        return $this;
    }

    public function setPreUserMod(?int $preUserMod)
    {
        $this->preUserMod = $preUserMod;
        return $this;
    }

    public function setPreDatMod(?DateTime $preDatMod)
    {
        $this->preDatMod = $preDatMod;
        return $this;
    }

    public function setPostStatus(?string $postStatus)
    {
        $this->postStatus = $postStatus;
        return $this;
    }

    public function setPostDescription(?string $postDescription)
    {
        $this->postDescription = $postDescription;
        return $this;
    }

    public function setPostUserMod(?int $postUserMod)
    {
        $this->postUserMod = $postUserMod;
        return $this;
    }

    public function setPostDatMod(?DateTime $postDatMod)
    {
        $this->postDatMod = $postDatMod;
        return $this;
    }

    public function setUser(SimpleUser $user)
    {
        $this->user = $user;
        return $this;
    }

    public function getModule(): string
    {
        return self::MODULE;
    }

    public function getScheme(): array
    {
        return AttendanceMapper::scheme();
    }

    public function getTable(): string
    {
        return self::TABLE;
    }
}
