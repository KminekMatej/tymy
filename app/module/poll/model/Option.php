<?php

namespace Tymy\Module\Poll\Model;

use Tymy\Module\Core\Model\BaseModel;
use Tymy\Module\Poll\Mapper\OptionMapper;

/**
 * Description of Option
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 20. 12. 2020
 */
class Option extends BaseModel
{
    public const MODULE = "poll";
    public const TABLE = "ask_items";

    private int $pollId;
    private string $caption;
    private string $type;

    public function getPollId(): int
    {
        return $this->pollId;
    }

    public function getCaption(): string
    {
        return $this->caption;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setPollId(int $pollId)
    {
        $this->pollId = $pollId;
        return $this;
    }

    public function setCaption(string $caption)
    {
        $this->caption = $caption;
        return $this;
    }

    public function setType(string $type)
    {
        $this->type = $type;
        return $this;
    }

    public function getModule(): string
    {
        return self::MODULE;
    }

    public function getScheme(): array
    {
        return OptionMapper::scheme();
    }

    public function getTable(): string
    {
        return self::TABLE;
    }
}
