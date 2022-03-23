<?php

namespace Tymy\Module\Poll\Manager;

use Nette\NotImplementedException;
use Tymy\Module\Core\Factory\ManagerFactory;
use Tymy\Module\Core\Manager\BaseManager;
use Tymy\Module\Core\Model\BaseModel;
use Tymy\Module\Poll\Mapper\VoteMapper;
use Tymy\Module\Poll\Model\Poll;
use Tymy\Module\Poll\Model\Vote;

/**
 * Description of VoteManager
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 21. 12. 2020
 */
class VoteManager extends BaseManager
{
    private Poll $poll;

    public function __construct(ManagerFactory $managerFactory)
    {
        parent::__construct($managerFactory);
        $this->idCol = null;
    }

    protected function getClassName(): string
    {
        return Vote::class;
    }

    protected function getScheme(): array
    {
        return VoteMapper::scheme();
    }

    public function setPoll(?Poll $poll = null)
    {
        $this->poll = $poll;
        return $this;
    }

    protected function allowCreate(?array &$data = null): void
    {
        //check consistency of userId and pollId (same user, same poll)

        $voteUserId = null;
        foreach ($data as &$vote) {
            if (empty($voteUserId) && !empty($vote["userId"])) {
                $voteUserId = $vote["userId"];
            }

            if (!empty($vote["userId"]) && !empty($voteUserId) && $vote["userId"] !== $voteUserId) {
                $this->respondBadRequest("All votes must be for the same user");
                $voteUserId = $vote["userId"];
            }


            if (!empty($vote["pollId"]) && $vote["pollId"] !== $this->poll->getId()) {
                $this->respondBadRequest("All votes must have the same poll id ({$this->poll->getId()})");
            }

            $vote["pollId"] = $this->poll->getId();
        }

        $vote["userId"] = $voteUserId ?? $this->user->getId();

        //check this user can vote
        if ($voteUserId === $this->user->getId() && !$this->poll->getCanVote()) {
            $this->respondForbidden("Cannot vote in this poll");
        }

        //check current user can vote as desired user
        if ($voteUserId !== $this->user->getId() && !$this->poll->getCanAlienVote()) {
            $this->respondForbidden("Cannot deputy vote in this poll");
        }

        //check if this user has already voted in unchangeable poll
        if (!$this->poll->getChangeableVotes() && $this->userVoted($this->poll, $voteUserId)) {
            $this->respondForbidden("Changing votes not allowed in this poll");
        }

        $this->checkNumberOfAnswers($this->poll, $data);

        //delete votes for this poll and user
        $this->database->table(Vote::TABLE)->where("quest_id", $this->poll->getId())->where("user_id", $voteUserId)->delete();
    }

    private function checkNumberOfAnswers(Poll $poll, array &$votes): void
    {
        if ($poll->getMinItems() == null && $poll->getMaxItems() == null) {
            return;
        }
        $answers = 0;
        foreach ($votes as $vote) {
            if (!empty($vote["booleanValue"]) || !empty($vote["stringValue"]) || !empty($vote["numericValue"])) {
                $answers++;
            }
        }

        if ($poll->getMinItems() && $answers < $poll->getMinItems()) {
            $this->respondBadRequest("Number of answers ($answers) must be greater than poll min limit ({$poll->getMinItems()}).");
        }
        if ($poll->getMaxItems() && $answers > $poll->getMaxItems()) {
            $this->respondBadRequest("Number of answers ($answers) mustn't be greater than poll max limit ({$poll->getMaxItems()}).");
        }
    }

    /**
     * Check if desired user already voted in this vote
     *
     * @param Poll $poll
     * @param int $userId
     * @return boolean
     */
    private function userVoted(Poll $poll, int $userId)
    {
        foreach ($poll->getVotes() as $vote) {
            /* @var $vote Vote */
            if ($vote->getUserId() === $userId) {
                return true;
            }
        }
        return false;
    }

    public function canEdit($entity, $userId): bool
    {
        return false;    //permissions is checked on parent
    }

    public function canRead($entity, $userId): bool
    {
        return false;    //permissions is checked on parent
    }

    public function create(array $data, ?int $resourceId = null): BaseModel
    {
        if (empty($this->poll)) {
            $this->responder->E4005_OBJECT_NOT_FOUND(Poll::MODULE, $resourceId);
        }

        $this->allowCreate($data);

        $createdVotes = [];
        //add votes
        foreach ($data as $vote) {
            $createdVotes[] = $this->map($this->createByArray($vote));
        }

        return array_shift($createdVotes);
    }

    public function delete(int $resourceId, ?int $subResourceId = null): int
    {
        throw new NotImplementedException("Not implemented yet");
    }

    public function getAllowedReaders(BaseModel $record): array
    {
        return $this->getAllUserIds();
    }

    public function read(int $resourceId, ?int $subResourceId = null): BaseModel
    {
        throw new NotImplementedException("Not implemented yet");
    }

    public function update(array $data, int $resourceId, ?int $subResourceId = null): BaseModel
    {
        throw new NotImplementedException("Not implemented yet");
    }
}
