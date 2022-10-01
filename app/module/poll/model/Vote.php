<?php

namespace Tymy\Module\Poll\Model;

use Nette\Utils\DateTime;
use Tymy\Module\Core\Model\BaseModel;
use Tymy\Module\Poll\Mapper\VoteMapper;

/**
 * Description of Vote
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 20. 12. 2020
 */
class Vote extends BaseModel
{
    public const MODULE = "poll";
    public const TABLE = "ask_votes";

    private int $pollId;
    private int $userId;
    private int $optionId;
    private ?int $numericValue = null;
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

    public function getNumericValue(): ?int
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

    public function setPollId(int $pollId)
    {
        $this->pollId = $pollId;
        return $this;
    }

    public function setUserId(int $userId)
    {
        $this->userId = $userId;
        return $this;
    }

    public function setOptionId(int $optionId)
    {
        $this->optionId = $optionId;
        return $this;
    }

    public function setNumericValue(?int $numericValue)
    {
        $this->numericValue = $numericValue;
        return $this;
    }

    public function setBooleanValue($booleanValue)
    {
        switch (true) {
            case is_string($booleanValue):
                $this->booleanValue = in_array(strtoupper($booleanValue), ["YES", "TRUE", "ANO"]);
                break;
            case is_int($booleanValue):
                $this->booleanValue = (bool) $booleanValue;
                break;
            case is_bool($booleanValue):
                $this->booleanValue = $booleanValue;
                break;
            default:
                $this->booleanValue = null;
                break;
        }
        return $this;
    }

    public function setStringValue(?string $stringValue)
    {
        $this->stringValue = $stringValue;
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
        return VoteMapper::scheme();
    }

    public function getTable(): string
    {
        return self::TABLE;
    }
}
