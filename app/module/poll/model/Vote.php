<?php

namespace Tymy\Module\Poll\Model;

use Nette\Utils\DateTime;
use Tymy\Module\Core\Model\BaseModel;
use Tymy\Module\Poll\Mapper\VoteMapper;

/**
 * Description of Vote
 */
class Vote extends BaseModel
{
    public const MODULE = "poll";
    public const TABLE = "ask_votes";

    private int $pollId;
    private int $userId;
    private int $optionId;
    private ?float $numericValue = null;
    private ?bool $booleanValue = null;
    private ?string $stringValue = null;
    private ?int $updatedById = null;
    private ?DateTime $updatedAt = null;

    public function getPollId(): int
    {
        return $this->pollId;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getOptionId(): int
    {
        return $this->optionId;
    }

    public function getNumericValue(): ?float
    {
        return $this->numericValue;
    }

    public function getBooleanValue(): ?bool
    {
        return $this->booleanValue;
    }

    public function getStringValue(): ?string
    {
        return $this->stringValue;
    }

    public function getUpdatedById(): ?int
    {
        return $this->updatedById;
    }

    public function getUpdatedAt(): ?DateTime
    {
        return $this->updatedAt;
    }

    public function setPollId(int $pollId): static
    {
        $this->pollId = $pollId;
        return $this;
    }

    public function setUserId(int $userId): static
    {
        $this->userId = $userId;
        return $this;
    }

    public function setOptionId(int $optionId): static
    {
        $this->optionId = $optionId;
        return $this;
    }

    public function setNumericValue(?float $numericValue): static
    {
        $this->numericValue = $numericValue;
        return $this;
    }

    public function setBooleanValue($booleanValue): static
    {
        $this->booleanValue = match (true) {
            is_string($booleanValue) => in_array(strtoupper($booleanValue), ["YES", "TRUE", "ANO"]),
            is_int($booleanValue) => (bool) $booleanValue,
            is_bool($booleanValue) => $booleanValue,
            default => null,
        };
        return $this;
    }

    public function setStringValue(?string $stringValue): static
    {
        $this->stringValue = $stringValue;
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

    public function getModule(): string
    {
        return self::MODULE;
    }

    /**
     * @return mixed[]
     */
    public function getScheme(): array
    {
        return VoteMapper::scheme();
    }

    public function getTable(): string
    {
        return self::TABLE;
    }
}
