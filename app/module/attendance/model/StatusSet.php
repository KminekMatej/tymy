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
    public const PRE = "pre";
    public const POST = "post";

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

    /**
     * @return mixed[]
     */
    public function getStatuses(): array
    {
        return $this->statuses;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function setWebname(string $webname): static
    {
        $this->webname = $webname;
        return $this;
    }

    /**
     * @param mixed[] $statuses
     */
    public function setStatuses(array $statuses): static
    {
        $this->statuses = $statuses;
        return $this;
    }

    public function addStatus(Status $status): void
    {
        $this->statuses[] = $status;
    }

    public function getModule(): string
    {
        return Attendance::MODULE;
    }

    /**
     * @return \Tymy\Module\Core\Model\Field[]
     */
    public function getScheme(): array
    {
        return StatusSetMapper::scheme();
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
            "webname" => $this->getWebname(),
            "statuses" => $this->arrayToJson($this->statuses)
        ];
    }
}
