<?php

namespace Tymy\Module\Poll\Model;

use Nette\Utils\DateTime;
use Tymy\Module\Core\Model\BaseModel;
use Tymy\Module\Poll\Mapper\PollMapper;

/**
 * Description of Poll
 */
class Poll extends BaseModel
{
    public const MODULE = "poll";
    public const TABLE = "ask_quests";
    public const STATUS_DESIGN = "DESIGN";
    public const STATUS_OPENED = "OPENED";
    public const STATUS_CLOSED = "CLOSED";
    public const STATUS_HIDDEN = "HIDDEN";
    public const RESULTS_NEVER = "NEVER";
    public const RESULTS_ALWAYS = "ALWAYS";
    public const RESULTS_AFTER_VOTE = "AFTER_VOTE";
    public const RESULTS_WHEN_CLOSED = "WHEN_CLOSED";

    private ?int $createdById = null;
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

    public function getCreatedById(): ?int
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

    /**
     * @return \Tymy\Module\Poll\Model\Option[]
     */
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

    /**
     * @return mixed[]
     */
    public function getOrderedVotes(): array
    {
        return $this->orderedVotes;
    }

    /**
     * @return mixed[]
     */
    public function getMyVotes(): array
    {
        return $this->myVotes;
    }

    public function setCreatedById(?int $createdById): static
    {
        $this->createdById = $createdById;
        return $this;
    }

    public function setCreatedAt(?DateTime $createdAt): static
    {
        $this->createdAt = $createdAt;
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

    public function setCaption(?string $caption): static
    {
        $this->caption = $caption;
        return $this;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function setDescriptionHtml(?string $descriptionHtml): static
    {
        $this->descriptionHtml = $descriptionHtml;
        return $this;
    }

    public function setMinItems(?int $minItems): static
    {
        $this->minItems = $minItems;
        return $this;
    }

    public function setMaxItems(?int $maxItems): static
    {
        $this->maxItems = $maxItems;
        return $this;
    }

    public function setChangeableVotes(string $changeableVotes): static
    {
        $this->changeableVotes = (bool) $changeableVotes;
        return $this;
    }

    public function setAnonymousResults($anonymousResults): static
    {
        $this->anonymousResults = (bool) $anonymousResults;
        return $this;
    }

    public function setShowResults(string $showResults): static
    {
        $this->showResults = $showResults;
        return $this;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function setResultRightName(?string $resultRightName): static
    {
        $this->resultRightName = $resultRightName;
        return $this;
    }

    public function setVoteRightName(?string $voteRightName): static
    {
        $this->voteRightName = $voteRightName;
        return $this;
    }

    public function setAlienVoteRightName(?string $alienVoteRightName): static
    {
        $this->alienVoteRightName = $alienVoteRightName;
        return $this;
    }

    public function setOrderFlag(?int $orderFlag): static
    {
        $this->orderFlag = $orderFlag;
        return $this;
    }

    public function setWebName(?string $webName): static
    {
        $this->webName = $webName;
        return $this;
    }

    /**
     * @param \Tymy\Module\Poll\Model\Option[] $options
     */
    public function setOptions(array $options): static
    {
        $this->options = $options;
        return $this;
    }

    /**
     * @param \Tymy\Module\Poll\Model\Vote[] $votes
     */
    public function setVotes(array $votes): static
    {
        $this->votes = $votes;
        return $this;
    }

    public function setCanSeeResults(bool $canSeeResults): static
    {
        $this->canSeeResults = $canSeeResults;
        return $this;
    }

    public function setCanVote(bool $canVote): static
    {
        $this->canVote = $canVote;
        return $this;
    }

    public function setCanAlienVote(bool $canAlienVote): static
    {
        $this->canAlienVote = $canAlienVote;
        return $this;
    }

    public function setVoted(bool $voted): static
    {
        $this->voted = $voted;
        return $this;
    }

    public function setVotePending(bool $votePending): void
    {
        $this->votePending = $votePending;
    }

    /**
     * @param mixed[] $orderedVotes
     */
    public function setOrderedVotes(array $orderedVotes): void
    {
        $this->orderedVotes = $orderedVotes;
    }

    /**
     * @param mixed[] $myVotes
     */
    public function setMyVotes(array $myVotes): void
    {
        $this->myVotes = $myVotes;
    }

//adders

    public function addVote(Vote $vote, int $userId): static
    {
        $this->votes[] = $vote;

        if (!array_key_exists($vote->getUserId(), $this->orderedVotes)) {
            $this->orderedVotes[$vote->getUserId()] = [];
        }
        $this->orderedVotes[$vote->getUserId()][$vote->getOptionId()] = $vote;

        if (!$this->getAnonymousResults() && $vote->getUserId() === $userId) {
            $this->myVotes[$vote->getOptionId()] = $vote;
        }

        return $this;
    }

    public function getModule(): string
    {
        return Poll::MODULE;
    }

    /**
     * @return \Tymy\Module\Core\Model\Field[]
     */
    public function getScheme(): array
    {
        return PollMapper::scheme();
    }

    public function getTable(): string
    {
        return Poll::TABLE;
    }

    /**
     * @return mixed[]
     */
    public function jsonSerialize(): array
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
