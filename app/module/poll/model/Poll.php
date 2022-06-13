<?php

namespace Tymy\Module\Poll\Model;

use Nette\Utils\DateTime;
use Tymy\Module\Core\Model\BaseModel;
use Tymy\Module\Poll\Mapper\PollMapper;

/**
 * Description of Poll
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 20. 12. 2020
 */
class Poll extends BaseModel
{
    public const MODULE = "poll";
    public const TABLE = "ask_quests";
    public const STATUS_DESIGN = "DESIGN";
    public const STATUS_OPENED = "OPENED";
    public const STATUS_CLOSED = "CLOSED";
    public const RESULTS_NEVER = "NEVER";
    public const RESULTS_ALWAYS = "ALWAYS";
    public const RESULTS_AFTER_VOTE = "AFTER_VOTE";
    public const RESULTS_WHEN_CLOSED = "WHEN_CLOSED";

    private int $createdById;
    private ?DateTime $createdAt = null;
    private ?int $updatedById = null;
    private ?DateTime $updatedAt = null;
    private ?string $caption = null;
    private ?string $description = null;
    private ?string $descriptionHtml = null;
    private ?int $minItems = null;
    private ?int $maxItems = null;
    private bool $changeableVotes;
    private bool $anonymousResults;
    private string $showResults;
    private string $status;
    private ?string $resultRightName = null;
    private ?string $voteRightName = null;
    private ?string $alienVoteRightName = null;
    private ?int $orderFlag = null;
    private ?string $webName = null;

    /** @var Option[] */
    private array $options = [];

    /** @var Vote[] */
    private array $votes = [];
    private array $orderedVotes = [];
    private array $myVotes = [];
    private bool $canSeeResults = false;
    private bool $canVote = false;
    private bool $canAlienVote = false;
    private bool $voted = false;
    private bool $votePending = false;
    public bool $fullyMapped = false;

    public function getCreatedById(): int
    {
        return $this->createdById;
    }

    public function getCreatedAt(): ?DateTime
    {
        return $this->createdAt;
    }

    public function getUpdatedById(): ?int
    {
        return $this->updatedById;
    }

    public function getUpdatedAt(): ?DateTime
    {
        return $this->updatedAt;
    }

    public function getCaption(): ?string
    {
        return $this->caption;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getDescriptionHtml(): ?string
    {
        return $this->descriptionHtml;
    }

    public function getMinItems(): ?int
    {
        return $this->minItems;
    }

    public function getMaxItems(): ?int
    {
        return $this->maxItems;
    }

    public function getChangeableVotes(): bool
    {
        return $this->changeableVotes;
    }

    public function getAnonymousResults(): bool
    {
        return $this->anonymousResults;
    }

    public function getShowResults(): string
    {
        return $this->showResults;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getResultRightName(): ?string
    {
        return $this->resultRightName;
    }

    public function getVoteRightName(): ?string
    {
        return $this->voteRightName;
    }

    public function getAlienVoteRightName(): ?string
    {
        return $this->alienVoteRightName;
    }

    public function getOrderFlag(): ?int
    {
        return $this->orderFlag;
    }

    public function getWebName(): ?string
    {
        return $this->webName;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    /** @return Vote[] */
    public function getVotes(): array
    {
        return $this->votes;
    }

    public function getCanSeeResults(): bool
    {
        return $this->canSeeResults;
    }

    public function getCanVote(): bool
    {
        return $this->canVote;
    }

    public function getCanAlienVote(): bool
    {
        return $this->canAlienVote;
    }

    public function getVoted(): bool
    {
        return $this->voted;
    }

    public function getVotePending(): bool
    {
        return $this->votePending;
    }

    public function getOrderedVotes(): array
    {
        return $this->orderedVotes;
    }

    public function getMyVotes(): array
    {
        return $this->myVotes;
    }

    public function setCreatedById(int $createdById)
    {
        $this->createdById = $createdById;
        return $this;
    }

    public function setCreatedAt(?DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
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

    public function setCaption(?string $caption)
    {
        $this->caption = $caption;
        return $this;
    }

    public function setDescription(?string $description)
    {
        $this->description = $description;
        return $this;
    }

    public function setDescriptionHtml(?string $descriptionHtml)
    {
        $this->descriptionHtml = $descriptionHtml;
        return $this;
    }

    public function setMinItems(?int $minItems)
    {
        $this->minItems = $minItems;
        return $this;
    }

    public function setMaxItems(?int $maxItems)
    {
        $this->maxItems = $maxItems;
        return $this;
    }

    public function setChangeableVotes(string $changeableVotes)
    {
        $this->changeableVotes = $changeableVotes ? true : false;
        return $this;
    }

    public function setAnonymousResults($anonymousResults)
    {
        $this->anonymousResults = $anonymousResults ? true : false;
        return $this;
    }

    public function setShowResults(string $showResults)
    {
        $this->showResults = $showResults;
        return $this;
    }

    public function setStatus(string $status)
    {
        $this->status = $status;
        return $this;
    }

    public function setResultRightName(?string $resultRightName)
    {
        $this->resultRightName = $resultRightName;
        return $this;
    }

    public function setVoteRightName(?string $voteRightName)
    {
        $this->voteRightName = $voteRightName;
        return $this;
    }

    public function setAlienVoteRightName(?string $alienVoteRightName)
    {
        $this->alienVoteRightName = $alienVoteRightName;
        return $this;
    }

    public function setOrderFlag(?int $orderFlag)
    {
        $this->orderFlag = $orderFlag;
        return $this;
    }

    public function setWebName(?string $webName)
    {
        $this->webName = $webName;
        return $this;
    }

    public function setOptions(array $options)
    {
        $this->options = $options;
        return $this;
    }

    public function setVotes(array $votes)
    {
        $this->votes = $votes;
        return $this;
    }

    public function setCanSeeResults(bool $canSeeResults)
    {
        $this->canSeeResults = $canSeeResults;
        return $this;
    }

    public function setCanVote(bool $canVote)
    {
        $this->canVote = $canVote;
        return $this;
    }

    public function setCanAlienVote(bool $canAlienVote)
    {
        $this->canAlienVote = $canAlienVote;
        return $this;
    }

    public function setVoted(bool $voted)
    {
        $this->voted = $voted;
        return $this;
    }

    public function setVotePending(bool $votePending): void
    {
        $this->votePending = $votePending;
    }

    public function setOrderedVotes(array $orderedVotes): void
    {
        $this->orderedVotes = $orderedVotes;
    }

    public function setMyVotes(array $myVotes): void
    {
        $this->myVotes = $myVotes;
    }

//adders

    public function addVote(Vote $vote, int $userId)
    {
        $this->votes[] = $vote;

        if (!array_key_exists($vote->getUserId(), $this->orderedVotes)) {
            $this->orderedVotes[$vote->getUserId()] = [];
        }
        $this->orderedVotes[$vote->getUserId()][$vote->getOptionId()] = $vote;

        if (!$this->getAnonymousResults() && $vote->getUserId() == $userId) {
            $this->myVotes[$vote->getOptionId()] = $vote;
        }

        return $this;
    }

    public function getModule(): string
    {
        return Poll::MODULE;
    }

    public function getScheme(): array
    {
        return PollMapper::scheme();
    }

    public function getTable(): string
    {
        return Poll::TABLE;
    }

    public function jsonSerialize()
    {
        return parent::jsonSerialize() + [
            "descriptionHtml" => $this->getDescriptionHtml(),
            "options" => $this->arrayToJson($this->getOptions()),
            "votes" => $this->arrayToJson($this->getVotes()),
            "canSeeResults" => $this->getCanSeeResults(),
            "canVote" => $this->getCanVote(),
            "canAlienVote" => $this->getCanAlienVote(),
            "voted" => $this->getVoted(),
            "mainMenu" => false, /** @breaking-change-avoid 13.6.2022  */
        ];
    }
}
