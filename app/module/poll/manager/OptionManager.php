<?php

namespace Tymy\Module\Poll\Manager;

use Nette\NotImplementedException;
use Tymy\Module\Core\Manager\BaseManager;
use Tymy\Module\Core\Model\BaseModel;
use Tymy\Module\Permission\Model\Privilege;
use Tymy\Module\Poll\Mapper\OptionMapper;
use Tymy\Module\Poll\Model\Option;
use Tymy\Module\Poll\Model\Poll;

/**
 * Description of OptionManager
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 21. 12. 2020
 */
class OptionManager extends BaseManager
{
    private PollManager $pollManager;
    private Option $option;

    public function setPollManager(PollManager $pollManager)
    {
        $this->pollManager = $pollManager;
        return $this;
    }

    protected function getClassName(): string
    {
        return Option::class;
    }

    protected function getScheme(): array
    {
        return OptionMapper::scheme();
    }

    public function canEdit($entity, $userId): bool
    {
        return false;    //permissions is checked on parent
    }

    public function canRead($entity, $userId): bool
    {
        return false;    //permissions is checked on parent
    }

    protected function allowCreate(?array &$data = null): void
    {
        if (!$this->user->isAllowed($this->user->getId(), Privilege::SYS("ASK.VOTE_UPDATE"))) {
            $this->respondForbidden();
        }

        $this->checkInputs($data);
    }

    protected function allowUpdate(?int $recordId = null, ?array &$data = null): void
    {
        if (!$this->user->isAllowed($this->user->getId(), Privilege::SYS("ASK.VOTE_UPDATE"))) {
            $this->respondForbidden();
        }

        if ($data && array_key_exists("pollId", $data)) {
            unset($data["pollId"]); //pollId is not changeable
        }
    }

    protected function allowDelete(?int $recordId): void
    {
        $this->allowUpdate($recordId);
    }


    public function createMultiple(array $options, ?int $resourceId = null): array
    {
        if (!$this->user->isAllowed($this->user->getId(), Privilege::SYS("ASK.VOTE_UPDATE"))) {
            $this->respondForbidden();
        }

        foreach ($options as $option) {
            $this->allowCreate($option);
        }

        $created = [];
        foreach ($options as $option) {
            $created[] = $this->map($this->createByArray($option));
        }

        return $created;
    }

    public function create(array $data, ?int $resourceId = null): BaseModel
    {
        $data["pollId"] = $resourceId;

        $this->allowCreate($data);

        return $this->map(parent::createByArray($data));
    }

    public function delete(int $resourceId, ?int $subResourceId = null): int
    {
        $this->allowPoll($resourceId);

        $this->allowDelete($subResourceId);

        return parent::deleteRecord($subResourceId);
    }

    public function getAllowedReaders(BaseModel $record): array
    {
        return $this->getAllUserIds();
    }

    public function read(int $resourceId, ?int $subResourceId = null): BaseModel
    {
        throw new NotImplementedException();
    }

    public function update(array $data, int $resourceId, ?int $subResourceId = null): BaseModel
    {
        $this->allowPoll($resourceId);

        $this->allowUpdate($subResourceId, $data);

        parent::updateByArray($subResourceId, $data);

        return $this->getById($subResourceId);
    }

    /**
     * Get options of specified poll
     *
     * @param int $pollId
     * @return Option[]
     */
    public function getPollOptions(int $pollId): array
    {
        $this->allowPoll($pollId);

        return $this->mapAll($this->database->table(Option::TABLE)->where("quest_id", $pollId)->fetchAll());
    }

    /**
     * Delete multiple poll options
     * @param int $pollId
     * @param array $ids
     * @return void
     */
    public function deleteOptions(int $pollId, array $ids): void
    {
        $this->allowPoll($pollId);
        $this->allowUpdate($pollId);

        $this->database->table($this->getTable())->where("quest_id", $pollId)->where("id", $ids)->delete();
    }

    private function allowPoll(int $pollId): void
    {
        $pollExists = $this->database->table(Poll::TABLE)->where("id", $pollId)->count("id") > 0;
        if (!$pollExists) {
            $this->respondNotFound();
        }
    }
}
