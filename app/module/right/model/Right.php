<?php

namespace Tymy\Module\Right\Model;

use Nette\Utils\DateTime;
use Tymy\Module\Core\Model\BaseModel;
use Tymy\Module\Right\Mapper\RightMapper;

/**
 * Description of Right
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 4. 8. 2020
 */
class Right extends BaseModel
{
    public const TABLE = "rights_cache";
    public const MODULE = "right";

    private string $type;
    private string $name;
    private bool $allowed;

    public function getType(): string
    {
        return $this->type;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getAllowed(): bool
    {
        return $this->allowed;
    }

    public function setType(string $type)
    {
        $this->type = $type;
        return $this;
    }

    public function setName(string $name)
    {
        $this->name = $name;
        return $this;
    }

    public function setAllowed(string $allowed)
    {
        $this->allowed = $allowed == "YES";
        return $this;
    }

    public function getModule(): string
    {
        return self::MODULE;
    }

    public function getScheme(): array
    {
        return RightMapper::scheme();
    }

    public function getTable(): string
    {
        return self::TABLE;
    }
}
