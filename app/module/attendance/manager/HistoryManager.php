<?php

namespace Tymy\Module\Attendance\Manager;

use Nette\Database\IRow;
use Nette\Database\Table\ActiveRow;
use Nette\NotImplementedException;
use Tymy\Module\Attendance\Mapper\HistoryMapper;
use Tymy\Module\Attendance\Model\History;
use Tymy\Module\Core\Factory\ManagerFactory;
use Tymy\Module\Core\Manager\BaseManager;
use Tymy\Module\Core\Model\BaseModel;
use Tymy\Module\Event\Model\Event;
use Tymy\Module\Permission\Model\Privilege;
use Tymy\Module\User\Manager\UserManager;

/**
 * Description of HistoryManager
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 9. 10. 2020
 */
class HistoryManager extends BaseManager
{
    private UserManager $userManager;

    public function __construct(ManagerFactory $managerFactory, UserManager $userManager)
    {
        parent::__construct($managerFactory);
        $this->userManager = $userManager;
        $this->idCol = null;
    }

    /**
     *
     * @param ActiveRow $row
     * @param bool $force
     * @return History|null
     */
    public function map(?IRow $row, $force = false): ?BaseModel
    {
        if (!$row) {
            return null;
        }

        /* @var $history History */
        /* @var $row ActiveRow */
        $history = parent::map($row, $force);

        $history->setUser($this->userManager->getSimpleUser($history->getUserId()));

        if ($history->getUpdatedById()) {
            $history->setUpdatedBy($this->userManager->getSimpleUser($history->getUpdatedById()));
        }


        return $history;
    }

    protected function getClassName(): string
    {
        return History::class;
    }

    protected function getScheme(): array
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
     * @param int $userId
     * @return bool
     */
    public function canRead($entity, $userId): bool
    {
        return $this->allowRead($entity->getEventId());
    }

    protected function allowRead(?int $recordId = null): void
    {
        $eventRow = $this->database->table(Event::TABLE)->where("id", $recordId)->fetch();
        if (!$eventRow) {
            $this->responder->E4005_OBJECT_NOT_FOUND(Event::MODULE, $recordId);
        }

        $eventReadRightName = $eventRow->view_rights;
        if ($eventReadRightName && !$this->user->isAllowed($this->user->getId(), Privilege::USR($eventReadRightName))) {
            $this->responder->E4001_VIEW_NOT_PERMITTED(Event::MODULE, $recordId);
        }
    }

    public function getEventHistory($eventId): array
    {
        return $this->mapAll($this->database->query("SELECT `attend_history`.* FROM `attend_history` LEFT JOIN `users` ON `attend_history`.`user_id` = `users`.`id` WHERE (`event_id` = ?) AND (`users`.`status` != 'DELETED') ORDER BY `dat_mod` DESC", $eventId)->fetchAll());
    }

    public function create(array $data, ?int $resourceId = null): BaseModel
    {
        throw new NotImplementedException("Not implemented yet");
    }

    public function delete(int $resourceId, ?int $subResourceId = null): int
    {
        throw new NotImplementedException("Not implemented yet");
    }

    public function getAllowedReaders(BaseModel $record): array
    {
        return $this->getAllUserIds(); //everyone can read
    }

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