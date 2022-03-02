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
    public const TABLE = "status_set";

    private string $name;
    private string $webname;
    private array $statuses = [];

    public function getName(): string
    {
        return $this->name;
    }

    public function getWebname(): string
    {
        return $this->webname;
    }

    public function getStatuses(): array
    {
        return $this->statuses;
    }

    public function setName(string $name)
    {
        $this->name = $name;
        return $this;
    }

    public function setWebname(string $webname)
    {
        $this->webname = $webname;
        return $this;
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
            "webname" => $this->getWebname(),
            "statuses" => $this->arrayToJson($this->statuses)
        ];
    }
}
