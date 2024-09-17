<?php

namespace Tymy\Module\Poll\Model;

use Tymy\Module\Core\Model\BaseModel;
use Tymy\Module\Poll\Mapper\OptionMapper;

/**
 * Description of Option
 */
class Option extends BaseModel
{
    public const MODULE = "poll";
    public const TABLE = "ask_items";
    public const TYPE_NUMBER = "NUMBER";
    public const TYPE_TEXT = "TEXT";
    public const TYPE_BOOLEAN = "BOOLEAN";

    private int $pollId;
    private ?string $caption = null;
    private string $type;

    public function getPollId(): int
    {
        return $this->pollId;
    }

    public function getCaption(): ?string
    {
        return $this->caption;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setPollId(int $pollId): static
    {
        $this->pollId = $pollId;
        return $this;
    }

    public function setCaption(?string $caption): static
    {
        $this->caption = $caption;
        return $this;
    }

    public function setType(string $type): static
    {
        $this->type = $type;
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
        return OptionMapper::scheme();
    }

    public function getTable(): string
    {
        return self::TABLE;
    }
}
