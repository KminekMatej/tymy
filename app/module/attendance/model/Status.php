<?php

namespace Tymy\Module\Attendance\Model;

use Nette\Utils\DateTime;
use Tymy\Module\Attendance\Mapper\StatusMapper;
use Tymy\Module\Core\Model\BaseModel;

/**
 * Description of Status
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 4. 11. 2020
 */
class Status extends BaseModel
{
    public const TABLE = "status";
    public const MODULE = "attendance";

    private string $code;
    private string $color = "d9ff00";
    private ?string $caption = null;
    private ?string $icon = null;
    private int $statusSetId;
    private ?int $order = null;
    private ?int $updatedById = null;
    private ?DateTime $updatedAt = null;
    private string $statusSetName;

    public function getCode(): string
    {
        return $this->code;
    }

    public function getColor(): string
    {
        return $this->color;
    }

    public function getCaption(): ?string
    {
        return $this->caption;
    }

    public function getIcon(): string
    {
        return $this->icon ?? 'fa-solid fa-question fa-beat-fade';
    }

    public function getStatusSetId(): int
    {
        return $this->statusSetId;
    }

    public function getOrder(): ?int
    {
        return $this->order;
    }

    public function getUpdatedById(): ?int
    {
        return $this->updatedById;
    }

    public function getUpdatedAt(): ?DateTime
    {
        return $this->updatedAt;
    }

    public function getStatusSetName(): string
    {
        return $this->statusSetName;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;
        return $this;
    }

    public function setColor(string $color): static
    {
        $this->color = $color;
        return $this;
    }

    public function setCaption(?string $caption): static
    {
        $this->caption = $caption;
        return $this;
    }

    public function setIcon(?string $icon): static
    {
        $this->icon = $icon;
        return $this;
    }

    public function setStatusSetId(int $statusSetId): static
    {
        $this->statusSetId = $statusSetId;
        return $this;
    }

    public function setOrder(?int $order)
    {
        $this->order = $order;
        return $this;
    }

    public function setUpdatedById(?int $updatedById): static
    {
        $this->updatedById = $updatedById;
        return $this;
    }

    public function setUpdatedAt(?DateTime $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function setStatusSetName(string $statusSetName): static
    {
        $this->statusSetName = $statusSetName;
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
        return StatusMapper::scheme();
    }

    public function getTable(): string
    {
        return self::TABLE;
    }
}
