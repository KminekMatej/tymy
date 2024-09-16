<?php

namespace Tymy\Module\Settings\Model;

use Nette\Utils\DateTime;
use Tymy\Module\Core\Model\BaseModel;
use Tymy\Module\Settings\Mapper\ICalItemMapper;

/**
 * Description of ICalItem
 */
class ICalItem extends BaseModel
{
    public const MODULE = "settings";
    public const TABLE = "ical_item";

    private DateTime $created;
    private ?int $createdUserId = null;
    private int $icalId;
    private int $statusId;

    public function getCreated(): DateTime
    {
        return $this->created;
    }

    public function getCreatedUserId(): ?int
    {
        return $this->createdUserId;
    }

    public function getIcalId(): int
    {
        return $this->icalId;
    }

    public function getStatusId(): int
    {
        return $this->statusId;
    }

    public function setCreated(DateTime $created): static
    {
        $this->created = $created;
        return $this;
    }

    public function setCreatedUserId(?int $createdUserId): static
    {
        $this->createdUserId = $createdUserId;
        return $this;
    }

    public function setIcalId(int $icalId): static
    {
        $this->icalId = $icalId;
        return $this;
    }

    public function setStatusId(int $statusId): static
    {
        $this->statusId = $statusId;
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
        return ICalItemMapper::scheme();
    }

    public function getTable(): string
    {
        return self::TABLE;
    }
}
