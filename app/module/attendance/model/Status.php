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
    public const TABLE = "statuses";
    public const MODULE = "attendance";

    private string $code;
    private string $color = "d9ff00";
    private ?string $caption = null;
    private int $statusSetId;
    private ?int $updatedById = null;
    private ?DateTime $updatedAt = null;

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

    public function getStatusSetId(): int
    {
        return $this->statusSetId;
    }

    public function getUpdatedById(): ?int
    {
        return $this->updatedById;
    }

    public function getUpdatedAt(): ?DateTime
    {
        return $this->updatedAt;
    }

    public function setCode(string $code)
    {
        $this->code = $code;
        return $this;
    }

    public function setColor(string $color)
    {
        $this->color = $color;
        return $this;
    }

    public function setCaption(?string $caption)
    {
        $this->caption = $caption;
        return $this;
    }

    public function setStatusSetId(int $statusSetId)
    {
        $this->statusSetId = $statusSetId;
        return $this;
    }

    public function setUpdatedById(?int $updatedById)
    {
        $this->updatedById = $updatedById;
        return $this;
    }

    public function setUpdatedAt(?DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getModule(): string
    {
        return self::MODULE;
    }

    public function getScheme(): array
    {
        return StatusMapper::scheme();
    }

    public function getTable(): string
    {
        return self::TABLE;
    }
}
