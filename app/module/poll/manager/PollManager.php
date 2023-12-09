<?php

namespace Tymy\Module\Poll\Manager;

use Nette\Database\IRow;
use Nette\Database\Table\ActiveRow;
use Nette\Utils\DateTime;
use Nette\Utils\Strings;
use Tymy\Module\Core\Factory\ManagerFactory;
use Tymy\Module\Core\Manager\BaseManager;
use Tymy\Module\Core\Model\BaseModel;
use Tymy\Module\Core\Service\BbService;
use Tymy\Module\Permission\Manager\PermissionManager;
use Tymy\Module\Permission\Model\Permission;
use Tymy\Module\Permission\Model\Privilege;
use Tymy\Module\Poll\Mapper\PollMapper;
use Tymy\Module\Poll\Model\Option;
use Tymy\Module\Poll\Model\Poll;
use Tymy\Module\Poll\Model\Vote;
use Tymy\Module\User\Manager\UserManager;

/**
 * Description of PollManager
 *
 * @RequestMapping(value = "/polls/{id}/votes", method = RequestMethod.POST)
 *
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 21. 12. 2020
 */
class PollManager extends BaseManager
{
    private ?Poll $poll = null;

    public function __construct(ManagerFactory $managerFactory, private OptionManager $optionManager, private VoteManager $voteManager, private PermissionManager $permissionManager, private UserManager $userManager)
    {
        parent::__construct($managerFactory);
        $this->optionManager->setPollManager($this);
    }

    protected function getClassName(): string
    {
        return Poll::class;
    }

    /**
     * @return \Tymy\Module\Core\Model\Field[]
     */
    public function getScheme(): array
    {
        return PollMapper::scheme();
    }

    public function map(?IRow $row, $force = false): ?BaseModel
    {
        if (empty($row)) {
            return null;
        }
        assert($row instanceof ActiveRow);

        $poll = parent::map($row, $force);
        assert($poll instanceof Poll);

        if ($poll->getDescription()) {
            $poll->setDescriptionHtml(BbService::bb2Html($poll->getDescription()));
        }

        $optionRows = $row->related(Option::TABLE, "quest_id")->fetchAll();

        if (!empty($optionRows)) {
            $poll->setOptions($this->optionManager->mapAll($optionRows));
        }

        $voteRows = $row->related(Vote::TABLE, "quest_id")->fetchAll();

        if (!empty($voteRows)) {
            $votes = $this->voteManager->mapAll($voteRows);
            $myVotes = [];
            $orderedVotes = [];

            foreach ($votes as $vote) {
                assert($vote instanceof Vote);
                if (!$poll->getAnonymousResults() && $vote->getUserId() == $this->user->getId()) {
                    $myVotes[$vote->getOptionId()] = $vote;
                }
                $orderedVotes[$vote->getUserId()][$vote->getOptionId()] = $vote;
            }

            $poll->setVotes($votes);
            $poll->setOrderedVotes($orderedVotes);
            $poll->setMyVotes($myVotes);
        }

        if ($poll->getStatus() == Poll::STATUS_OPENED && $poll->getCanVote() && !$poll->getVoted()) {
            $poll->setVotePending(true);
        }

        $poll->setWebName(Strings::webalize($poll->getId() . "-" . $poll->getCaption()));

        $poll->fullyMapped = true;

        $this->metaMap($poll);

        return $poll;
    }

    protected function metaMap(BaseModel &$model, $userId = null): void
    {
        assert($model instanceof Poll);
        if (!$model->fullyMapped) {   //hack - metaMap is usually called by parent map function, but its not completely mapped and lacks important informations from local mapper. So metMap() is called from local mapper after everything is mapped properly. This condition just avoids double calling of metaMap
            return;
        }

        if (empty($userId)) {
            $userId = $this->user->getId();
        }

        //set voted
        foreach ($model->getVotes() as $vote) {
            assert($vote instanceof Vote);
            if ($vote->getUserId() === $userId) {
                $model->setVoted(true);
                break;
            }
        }

        $hasVoteRights = empty($model->getVoteRightName()) || $this->user->isAllowed($userId, Privilege::USR($model->getVoteRightName()));
        $hasAlienVoteRights = !empty($model->getAlienVoteRightName()) && $this->user->isAllowed($userId, Privilege::USR($model->getAlienVoteRightName()));
        $hasResultRights = empty($model->getResultRightName()) || $this->user->isAllowed($userId, Privilege::USR($model->getResultRightName()));
        $resultsCanBeShown = $model->getShowResults() == Poll::RESULTS_ALWAYS ||
                ($model->getShowResults() == Poll::RESULTS_AFTER_VOTE && $model->getVoted()) ||
                ($model->getShowResults() == Poll::RESULTS_NEVER && $model->getCreatedById() === $userId) ||
                ($model->getShowResults() == Poll::RESULTS_WHEN_CLOSED && $model->getStatus() == Poll::STATUS_CLOSED);

        $voteAllowed = $model->getChangeableVotes() || !$model->getVoted();
        $pollOpened = $model->getStatus() === Poll::STATUS_OPENED;

        $model->setCanVote($pollOpened && $hasVoteRights && $voteAllowed);
        $model->setCanAlienVote($pollOpened && $hasAlienVoteRights && $voteAllowed);
        $model->setCanSeeResults($resultsCanBeShown && $hasResultRights);

        $this->anonymizeIfNeeded($model);
        $this->hideResultsIfNeeded($model);
    }

    public function canEdit($entity, $userId): bool
    {
        return $this->user->isAllowed($userId, Privilege::SYS("ASK.VOTE_UPDATE"));
    }

    public function canRead($entity, $userId): bool
    {
        return true;
    }

    protected function allowCreate(?array &$data = null): void
    {
        if (!$this->user->isAllowed($this->user->getId(), Privilege::SYS("ASK.VOTE_CREATE"))) {
            $this->respondForbidden();
        }

        $data["createdById"] = $this->user->getId();
        $data["createdAt"] = new DateTime();

        if (!isset($data["caption"])) {
            $data["caption"] = "New poll";
        }
        if (!isset($data["minItems"])) {
            $data["minItems"] = -1;
        }
        if (!isset($data["maxItems"])) {
            $data["maxItems"] = -1;
        }
        if ($data["maxItems"] < $data["minItems"]) {
            $this->respondBadRequest("Max Items must be bigger or equal to Min items");
        }
        $data["showResults"] ??= Poll::RESULTS_NEVER;
        $data["status"] ??= Poll::STATUS_DESIGN;
        $data["orderFlag"] ??= 0;

        $this->checkInputs($data);
    }

    protected function allowDelete(?int $recordId): void
    {
        if ($this->poll === null) {
            $this->responder->E4005_OBJECT_NOT_FOUND(Poll::MODULE, $recordId);
        }

        $this->allowRead($recordId);

        if (!$this->user->isAllowed($this->user->getId(), Privilege::SYS("ASK.VOTE_DELETE"))) {
            $this->respondForbidden();
        }
    }

    protected function allowRead(?int $recordId = null): void
    {
        if ($this->poll === null) {
            $this->responder->E4005_OBJECT_NOT_FOUND(Poll::MODULE, $recordId);
        }
    }

    protected function allowUpdate(?int $recordId = null, ?array &$data = null): void
    {
        $this->allowRead($recordId);

        if (!$this->user->isAllowed($this->user->getId(), Privilege::SYS("ASK.VOTE_UPDATE"))) {
            $this->respondForbidden();
        }

        $data["updatedById"] = $this->user->getId();
        $data["updatedAt"] = new DateTime();
    }

    public function create(array $data, ?int $resourceId = null): BaseModel
    {
        parent::toBoolData($data, ["anonymousResults", "changeableVotes"]);

        $this->allowCreate($data);

        return $this->map($this->createByArray($data));
    }

    public function delete(int $resourceId, ?int $subResourceId = null): int
    {
        $this->poll = $this->getById($resourceId);

        $this->allowDelete($resourceId);

        return parent::deleteRecord($resourceId);
    }

    /**
     * @return int[]
     */
    public function getAllowedReaders(BaseModel $record): array
    {
        return $this->getAllUserIds();
    }

    public function read(int $resourceId, ?int $subResourceId = null): BaseModel
    {
        $this->poll = $this->getById($resourceId);

        $this->allowRead($resourceId);

        return $this->poll;
    }

    public function update(array $data, int $resourceId, ?int $subResourceId = null): BaseModel
    {
        parent::toBoolData($data, ["anonymousResults", "changeableVotes"]);

        $this->poll = $this->getById($resourceId);

        $this->allowUpdate($resourceId);

        parent::updateByArray($resourceId, $data);

        return $this->getById($resourceId);
    }

    /**
     * Get list of polls, which currently logged user is allowed to see
     * @return Poll[]
     */
    public function getListUserAllowed(): array
    {
        $userPermissions = $this->permissionManager->getUserAllowedPermissionNames($this->userManager->getById($this->user->getId()), Permission::TYPE_USER);

        $selector = $this->database->table($this->getTable())
            ->where("status IN (?)", [Poll::STATUS_OPENED, Poll::STATUS_CLOSED])
            ->order("order_flag ASC");   //display only polls which are not in status DESIGN (display CLOSED and OPENED)

        if (!empty($userPermissions)) {
            $selector->where("result_rights IS NULL OR result_rights = '' OR result_rights IN ?", $userPermissions);
        } else {
            $selector->where("result_rights IS NULL OR result_rights = ''");
        }

        return $this->mapAll($selector->fetchAll());
    }

    /**
     * If supplied poll is set to have anonymouse results, then this function process all votes, and sets random userId to each vote and drops updatedAt and updatedById properties
     */
    private function anonymizeIfNeeded(Poll &$poll): void
    {
        if (!$poll->getAnonymousResults() || empty($poll->getVotes())) {
            return;
        }

        $cloakIds = [];

        foreach ($poll->getVotes() as &$vote) {
            do {
                $cloakId = random_int(100000, 200000);
            } while (in_array($cloakId, $cloakIds));
            $cloakIds[] = $cloakId;

            assert($vote instanceof Vote);
            $vote->setUserId($cloakId);
            $vote->setUpdatedAt(null);
            $vote->setUpdatedById(null);
        }
    }

    /**
     * If user is not allowed to see poll results, this function removes results from the poll
     */
    private function hideResultsIfNeeded(Poll &$poll): void
    {
        if (!$poll->getCanSeeResults()) {
            $poll->setVotes([]);
        }
    }

    /**
     * Get sum of all polls with pending vote
     *
     * @param Poll[] $polls
     */
    public function getWarnings(array $polls): int
    {
        $count = 0;
        foreach ($polls as $poll) {
            assert($poll instanceof Poll);
            if ($poll->getVotePending()) {
                $count++;
            }
        }

        return $count;
    }
}
