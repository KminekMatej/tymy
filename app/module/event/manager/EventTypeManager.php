<?php

namespace Tymy\Module\Event\Manager;

use Nette\Database\IRow;
use Nette\Database\Table\ActiveRow;
use Nette\NotImplementedException;
use Tymy\Module\Attendance\Manager\StatusManager;
use Tymy\Module\Attendance\Model\Status;
use Tymy\Module\Attendance\Model\StatusSet;
use Tymy\Module\Core\Factory\ManagerFactory;
use Tymy\Module\Core\Manager\BaseManager;
use Tymy\Module\Core\Model\BaseModel;
use Tymy\Module\Event\Mapper\EventTypeMapper;
use Tymy\Module\Event\Model\EventType;

/**
 * Description of EventTypeManager
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 8. 10. 2020
 */
class EventTypeManager extends BaseManager
{
    private ?EventType $eventType = null;
    private array $colorList;
    private StatusManager $statusManager;

    public function __construct(ManagerFactory $managerFactory, StatusManager $statusManager)
    {
        parent::__construct($managerFactory);
        $this->statusManager = $statusManager;
    }

    /**
     *
     * @param ActiveRow $row
     * @param bool $force
     * @return EventType|null
     */
    public function map(?IRow $row, $force = false): ?BaseModel
    {
        if (!$row) {
            return null;
        }

        /* @var $eventType EventType */
        /* @var $row ActiveRow */
        $eventType = parent::map($row, $force);

        $statuses = $this->statusManager->getByEventTypeId($row->id);

        if ($eventType->getPreStatusSetId()) {
            $eventType->setPreStatusSet($statuses[StatusSet::PRE]);
        }
        if ($eventType->getPostStatusSetId()) {
            $eventType->setPostStatusSet($statuses[StatusSet::POST]);
        }

        return $eventType;
    }

    protected function getClassName(): string
    {
        return EventType::class;
    }

    protected function getScheme(): array
    {
        return EventTypeMapper::scheme();
    }

    /** @todo */
    public function canEdit($entity, $userId): bool
    {
        return false;
    }

    public function canRead($entity, $userId): bool
    {
        return true;
    }

    protected function allowRead(?int $recordId = null): void
    {
        $this->eventType = $this->getById($recordId);

        if (!$this->canRead($this->eventType, $this->user->getId())) {
            $this->respondForbidden();
        }
    }

    /**
     * Get list of all event types, with their code as array keys
     * @return EventType[]
     */
    public function getIndexedList()
    {
        $types = $this->getList();
        $typesIndexed = [];

        foreach ($types as $type) {
            /* @var $type EventType */
            $typesIndexed[$type->getCode()] = $type;
        }

        return $typesIndexed;
    }

    public function getListUserAllowed($userId): array
    {
        //reading is not restricted
        return $this->getList();
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

    public function read(int $resourceId, ?int $subResourceId = null): BaseModel
    {
        $this->allowRead($resourceId);

        return $this->eventType;
    }

    public function update(array $data, int $resourceId, ?int $subResourceId = null): BaseModel
    {
        throw new NotImplementedException("Not implemented yet");
    }

    /**
     * Get Event type row from database, using its unique code or null, if this code has not been found
     *
     * @param string $code
     * @return ActiveRow|null
     */
    public function getByCode(string $code): ?ActiveRow
    {
        $typeRow = $this->database->table($this->getTable())->where("code", $code)->fetch();
        return $typeRow ? $typeRow : null;
    }

    /**
     * Get event type color, cached
     *
     * @param int $eventTypeId
     * @return string Hexadecimal color value, without leading hashtag
     */
    public function getEventTypeColor(int $eventTypeId): string
    {
        if (!isset($this->colorList)) {
            $this->colorList = $this->database->table($this->getTable())->fetchPairs("id", "color");
        }

        return $this->colorList[$eventTypeId] ?? "d8dee4";
    }
}
