<?php

namespace Tymy\Module\Attendance\Model;

use Nette\Utils\DateTime;
use Tymy\Module\Attendance\Mapper\HistoryMapper;
use Tymy\Module\Core\Model\BaseModel;
use Tymy\Module\User\Model\SimpleUser;

/**
 * Description of History
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 9. 10. 2020
 */
class History extends BaseModel
{
    public const TABLE = "attendance_history";
    public const MODULE = "event";
    public const TYPE_USER_ATTENDANCE_ENTRY = "UAE";

    private int $eventId;
    private int $userId;
    private ?DateTime $updatedAt = null;
    private ?int $updatedById = null;
    private string $entryType;
    private ?int $statusIdFrom = null;  //null if this is the first set
    private ?string $preStatusFrom = null;  //null if this is the first set
    private ?string $preDescFrom = null;
    private int $statusIdTo;
    private string $preStatusTo;
    private ?string $preDescTo = null;
    private ?SimpleUser $user = null;
    private ?SimpleUser $updatedBy = null;

    public function getEventId(): int
    {
        return $this->eventId;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getUpdatedAt(): ?\Nette\Utils\DateTime
    {
        return $this->updatedAt;
    }

    public function getUpdatedById(): ?int
    {
        return $this->updatedById;
    }

    public function getEntryType(): string
    {
        return $this->entryType;
    }

    public function getStatusIdFrom(): ?int
    {
        return $this->statusIdFrom;
    }

    public function getPreStatusFrom(): ?string
    {
        return $this->preStatusFrom;
    }

    public function getPreDescFrom(): ?string
    {
        return $this->preDescFrom;
    }

    public function getStatusIdTo(): int
    {
        return $this->statusIdTo;
    }

    public function getPreStatusTo(): string
    {
        return $this->preStatusTo;
    }

    public function getPreDescTo(): ?string
    {
        return $this->preDescTo;
    }

    public function getUser(): ?SimpleUser
    {
        return $this->user;
    }

    public function getUpdatedBy(): ?SimpleUser
    {
        return $this->updatedBy;
    }

    public function setEventId(int $eventId): static
    {
        $this->eventId = $eventId;
        return $this;
    }

    public function setUserId(int $userId): static
    {
        $this->userId = $userId;
        return $this;
    }

    public function setUpdatedAt(DateTime $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function setUpdatedById(?int $updatedById): static
    {
        $this->updatedById = $updatedById;
        return $this;
    }

    public function setEntryType(string $entryType): static
    {
        $this->entryType = $entryType;
        return $this;
    }

    public function setStatusIdFrom(?int $statusIdFrom): static
    {
        $this->statusIdFrom = $statusIdFrom;
        return $this;
    }

    public function setPreStatusFrom(?string $preStatusFrom): static
    {
        $this->preStatusFrom = $preStatusFrom;
        return $this;
    }

    public function setPreDescFrom(?string $preDescFrom): static
    {
        $this->preDescFrom = $preDescFrom;
        return $this;
    }

    public function setStatusIdTo(int $statusIdTo): static
    {
        $this->statusIdTo = $statusIdTo;
        return $this;
    }

    public function setPreStatusTo(?string $preStatusTo): static
    {
        $this->preStatusTo = $preStatusTo;
        return $this;
    }

    public function setPreDescTo(?string $preDescTo): static
    {
        $this->preDescTo = $preDescTo;
        return $this;
    }

    public function setUser(?SimpleUser $user = null): static
    {
        $this->user = $user;
        return $this;
    }

    public function setUpdatedBy(?SimpleUser $updatedBy): static
    {
        $this->updatedBy = $updatedBy;
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
        return HistoryMapper::scheme();
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
            "preStatusFrom" => $this->getPreStatusFrom(),
            "preStatusTo" => $this->getPreStatusTo(),
            "user" => $this->user !== null ? $this->user->jsonSerialize() : null,
            "updatedBy" => $this->updatedBy !== null ? $this->updatedBy->jsonSerialize() : null,
        ];
    }
}
