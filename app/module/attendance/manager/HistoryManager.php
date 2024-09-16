<?php

namespace Tymy\Module\Attendance\Manager;

use Nette\Database\IRow;
use Nette\Database\Table\ActiveRow;
use Nette\NotImplementedException;
use Tymy\Module\Attendance\Mapper\HistoryMapper;
use Tymy\Module\Attendance\Model\History;
use Tymy\Module\Attendance\Model\Status;
use Tymy\Module\Core\Factory\ManagerFactory;
use Tymy\Module\Core\Manager\BaseManager;
use Tymy\Module\Core\Model\BaseModel;
use Tymy\Module\Core\Model\Field;
use Tymy\Module\Event\Model\Event;
use Tymy\Module\User\Manager\UserManager;
use Tymy\Module\User\Model\User;

/**
 * @extends BaseManager<History>
 */
class HistoryManager extends BaseManager
{
    public function __construct(ManagerFactory $managerFactory, private UserManager $userManager)
    {
        parent::__construct($managerFactory);
        $this->idCol = null;
    }

    /**
     * @param ActiveRow|null $row
     * @return History|null
     */
    public function map(?IRow $row, bool $force = false): ?History
    {
        if ($row === null) {
            return null;
        }

        assert($row instanceof ActiveRow);
        $history = parent::map($row, $force);
        assert($history instanceof History);

        $history->setUser($this->userManager->getSimpleUser($history->getUserId()));

        if ($history->getUpdatedById()) {
            $history->setUpdatedBy($this->userManager->getSimpleUser($history->getUpdatedById()));
        }

        $history->setPreStatusTo($row->ref(Status::TABLE, "status_id_to")->code);
        if ($history->getStatusIdFrom()) {
            $history->setPreStatusFrom($row->ref(Status::TABLE, "status_id_from")->code);
        }

        return $history;
    }

    public function getClassName(): string
    {
        return History::class;
    }

    /**
     * @return Field[]
     */
    public function getScheme(): array
    {
        return HistoryMapper::scheme();
    }

    /** @todo */
    public function canEdit($entity, $userId): bool
    {
        return false;
    }

    /**
     * Check read permissions
     *
     * @param History $entity
     */
    public function canRead($entity, int $userId): bool
    {
        assert($entity instanceof History);
        $eventRow = $this->database->table(Event::TABLE)->where("id", $entity->getEventId())->fetch();
        return $eventRow->view_rights ? $this->user->isAllowed((string) $userId, "USR:{$eventRow->view_rights}") : true;
    }

    protected function allowRead(?int $recordId = null): void
    {
        $eventRow = $this->database->table(Event::TABLE)->where("id", $recordId)->fetch();
        if (!$eventRow instanceof ActiveRow) {
            $this->responder->E4005_OBJECT_NOT_FOUND(Event::MODULE, $recordId);
        }

        $eventReadRightName = $eventRow->view_rights;
        if ($eventReadRightName && !$this->user->isAllowed((string) $this->user->getId(), "USR:$eventReadRightName")) {
            $this->responder->E4001_VIEW_NOT_PERMITTED(Event::MODULE, $recordId);
        }
    }

    /**
     * Load all history records for users which are not DELETED
     *
     * @return History[]
     */
    public function getEventHistory(int $eventId): array
    {
        return $this->mapAll($this->database->table(History::TABLE)
                    ->where("event_id", $eventId)
                    ->where(User::TABLE . ".status != ?", User::STATUS_DELETED)
                    ->order("created DESC")
                    ->order("id ASC")
                    ->fetchAll());
    }

    public function create(array $data, ?int $resourceId = null): BaseModel
    {
        throw new NotImplementedException("Not implemented yet");
    }

    public function delete(int $resourceId, ?int $subResourceId = null): int
    {
        throw new NotImplementedException("Not implemented yet");
    }

    /**
     * @return int[]
     */
    public function getAllowedReaders(BaseModel $record): array
    {
        return $this->getAllUserIds(); //everyone can read
    }

    /**
     * @return History[]
     */
    public function readForEvent(int $eventId): array
    {
        $this->allowRead($eventId);

        return $this->getEventHistory($eventId);
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
