<?php

namespace Tymy\Module\Attendance\Model;

use Tymy\Module\Attendance\Mapper\StatusSetMapper;
use Tymy\Module\Core\Model\BaseModel;

/**
 * Description of StatusSet
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 4. 11. 2020
 */
class StatusSet extends BaseModel
{
    public const TABLE = "status_sets";

    private ?string $name = null;
    private array $statuses = [];

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name)
    {
        $this->name = $name;
        return $this;
    }

    public function getStatuses(): array
    {
        return $this->statuses;
    }

    public function setStatuses(array $statuses)
    {
        $this->statuses = $statuses;
        return $this;
    }

    public function addStatus(Status $status)
    {
        $this->statuses[] = $status;
    }

    public function getModule(): string
    {
        return Attendance::MODULE;
    }

    public function getScheme(): array
    {
        return StatusSetMapper::scheme();
    }

    public function getTable(): string
    {
        return self::TABLE;
    }

    public function jsonSerialize()
    {
        return parent::jsonSerialize() + [
            "statuses" => $this->arrayToJson($this->statuses)
        ];
    }
}
