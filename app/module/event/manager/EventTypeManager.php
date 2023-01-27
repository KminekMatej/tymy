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

    public function __construct(ManagerFactory $managerFactory, private StatusManager $statusManager)
    {
        parent::__construct($managerFactory);
    }

    /**
     *
     * @param ActiveRow|null $row
     * @return EventType|null
     */
    public function map(?IRow $row, bool $force = false): ?BaseModel
    {
        if ($row === null) {
            return null;
        }
        assert($row instanceof ActiveRow);

        $eventType = parent::map($row, $force);
        assert($eventType instanceof EventType);

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

    /**
     * @return \Tymy\Module\Core\Model\Field[]
     */
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
    public function getIndexedList(): array
    {
        $types = $this->getList();
        $typesIndexed = [];

        foreach ($types as $type) {
            assert($type instanceof EventType);
            $typesIndexed[$type->getCode()] = $type;
        }

        return $typesIndexed;
    }

    public function getList(?array $idList = null, string $idField = "id", ?int $limit = null, ?int $offset = null, ?string $order = null): array
    {
        return parent::getList($idList, $idField, $limit, $offset, $order ?: "order");
    }

    /**
     * @return \Tymy\Module\Core\Model\BaseModel[]
     */
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

    /**
     * @return int[]
     */
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
     */
    public function getByCode(string $code): ?ActiveRow
    {
        return $this->database->table($this->getTable())->where("code", $code)->fetch();
    }

    /**
     * Get event type color, cached
     *
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
