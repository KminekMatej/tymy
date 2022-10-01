<?php

namespace Tymy\Module\Attendance\Manager;

use Exception;
use Nette\Database\IRow;
use Nette\Database\Table\ActiveRow;
use Nette\NotImplementedException;
use Nette\Utils\DateTime;
use PDOException;
use Tymy\Module\Attendance\Mapper\AttendanceMapper;
use Tymy\Module\Attendance\Model\Attendance;
use Tymy\Module\Attendance\Model\History;
use Tymy\Module\Attendance\Model\Status;
use Tymy\Module\Core\Exception\DBException;
use Tymy\Module\Core\Factory\ManagerFactory;
use Tymy\Module\Core\Manager\BaseManager;
use Tymy\Module\Core\Model\BaseModel;
use Tymy\Module\Event\Model\Event;
use Tymy\Module\Permission\Manager\PermissionManager;
use Tymy\Module\Permission\Model\Privilege;
use Tymy\Module\User\Manager\UserManager;

/**
 * Description of AttendanceManager
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 21. 9. 2020
 */
class AttendanceManager extends BaseManager
{
    private UserManager $userManager;
    private HistoryManager $historyManager;
    private PermissionManager $permissionManager;
    private ?ActiveRow $eventRow;
    private array $myAttendances;


    public function __construct(ManagerFactory $managerFactory, UserManager $userManager, PermissionManager $permissionManager, HistoryManager $historyManager)
    {
        parent::__construct($managerFactory);
        $this->userManager = $userManager;
        $this->permissionManager = $permissionManager;
        $this->historyManager = $historyManager;
    }

    /**
     * Get attendance using event and user id
     * @param int $eventId
     * @param int $userId
     * @return Attendance
     */
    public function getByEventUserId(int $eventId, int $userId)
    {
        return $this->map($this->database->table($this->getTable())->where("event_id", $eventId)->where("user_id", $userId)->fetch());
    }

    /**
     * Get array of attendanced related to events
     *
     * @param array $eventIds
     * @return array
     */
    public function getByEvents(array $eventIds): array
    {
        $allRows = $this->database->table($this->getTable())->where("event_id", $eventIds)->fetchAll();
        $byEventId = [];
        foreach ($allRows as $row) {
            if (!array_key_exists($row->event_id, $byEventId)) {
                $byEventId[$row->event_id] = [];
            }

            $byEventId[$row->event_id][$row->user_id] = $this->map($row);
        }

        return $byEventId;
    }

    /**
     * Maps one active row to object
     * @param ActiveRow|false $row
     * @param bool $force True to skip cache
     * @return Attendance|null
     */
    public function map(?IRow $row, bool $force = false): ?BaseModel
    {
        /* @var $row ActiveRow */
        if ($row === null) {
            return null;
        }

        /* @var $attendance Attendance */
        $attendance = parent::map($row, $force);

        if ($attendance->getPreStatusId()) {
            $attendance->setPreStatus($row->ref(Status::TABLE, "pre_status_id")->code);
        }
        if ($attendance->getPostStatusId()) {
            $attendance->setPostStatus($row->ref(Status::TABLE, "post_status_id")->code);
        }

        $attendance->setUser($this->userManager->getSimpleUser($attendance->getUserId()));

        return $attendance;
    }

    protected function getClassName(): string
    {
        return Attendance::class;
    }

    protected function getScheme(): array
    {
        return AttendanceMapper::scheme();
    }

    /**
     * Check edit permission
     * @param Attendance $entity
     * @param int $userId
     * @return bool
     */
    public function canEdit($entity, $userId): bool
    {
        return $entity->getUserId() == $userId;
    }

    /**
     * Check read permission
     * @param Attendance $entity
     * @param int $userId
     * @return bool
     */
    public function canRead($entity, $userId): bool
    {
        return true;
    }

    /**
     * Get user ids allowed to read given attendance
     * @param Attendance $record
     * @return int[]
     */
    public function getAllowedReaders(BaseModel $record): array
    {
        /* @var $record Attendance */
        return $this->getAllUserIds();
    }

    protected function allowCreate(?array &$data = null): void
    {
        $preStatus = $data["preStatusId"] ?? $data["preStatus"] ?? null;
        $postStatus = $data["postStatusId"] ?? $data["postStatus"] ?? null;

        if (!$preStatus && !$postStatus) {
            $this->respondBadRequest("Event pre or post status in attendance entry must be provided");
        }

        if ($preStatus) {
            $data["preStatusId"] = is_numeric($preStatus) ? $preStatus : $this->getPreStatusId($data["eventId"], $preStatus);
            if (!$data["preStatusId"]) {
                $this->responder->E4008_CHILD_NOT_RELATED_TO_PARENT("Status", $preStatus, Event::MODULE, $data["eventId"]);
            }
        }
        if ($postStatus) {
            $data["postStatusId"] = is_numeric($postStatus) ? $postStatus : $this->getPostStatusId($data["eventId"], $postStatus);
            if (!$data["postStatusId"]) {
                $this->responder->E4008_CHILD_NOT_RELATED_TO_PARENT("Status", $postStatus, Event::MODULE, $data["eventId"]);
            }
        }

        unset($data["preUserMod"]); //these values an be set only programatically
        unset($data["preDatMod"]);
        unset($data["postUserMod"]);
        unset($data["postDatMod"]);

        $now = new DateTime();
        if ($preStatus) {
            $data["preUserMod"] = $this->user->getId();
            $data["preDatMod"] = $now;
        }

        if ($postStatus) {
            // this is result entry, check rights
            $this->allowSetResult();

            $data["postUserMod"] = $this->user->getId();
            $data["postDatMod"] = $now;
        } else {
            //creating or changing plan
            $this->allowAttend($data);
        }
    }

    /**
     * Check permissions if user, specified in data, can create attendance result for this event
     */
    private function allowSetResult()
    {
        $resultRightName = $this->eventRow->result_rights;
        if ($resultRightName) {
            if (!$this->user->isAllowed($this->user->getId(), Privilege::USR($resultRightName))) {
                $this->respondForbidden();
            }
        } elseif (!$this->user->isAllowed($this->user->getId(), Privilege::SYS("EVE_ATT_UPDATE"))) {
            $this->respondForbidden();
        }
    }

    /**
     * Check permissions if user, specified in data, can create attendance for this event
     *
     * @param array $data
     */
    private function allowAttend(array $data)
    {
        if ($this->user->getId() !== $data["userId"] && !$this->user->isAllowed($this->user->getId(), Privilege::SYS("ATT_UPDATE"))) {
            $this->respondForbidden();
        }

        $planRightName = $this->eventRow->plan_rights;
        if ($planRightName && !$this->user->isAllowed($this->user->getId(), Privilege::USR($planRightName))) {
            $this->respondForbidden();
        }
    }

    /**
     * Check if supplied PRE status can be assigned to this eventId and if can, return correct preStatusId (transforming code to id)
     * (Check using event_type and so on)
     *
     * @param int $eventId
     * @param int|string $preStatus Either id or code
     * @return int|null Return correct statusId or null for invalid code
     */
    private function getPreStatusId(int $eventId, $preStatus): ?int
    {
        $allowedStatuses = $this->database->query("SELECT status.id, status.code FROM status "
                . "LEFT JOIN status_set ON status_set.id=status.status_set_id "
                . "LEFT JOIN event_types ON event_types.pre_status_set_id=status_set.id "
                . "LEFT JOIN events ON events.event_type_id=event_types.id WHERE events.id=?", $eventId)->fetchPairs("id", "code");

        if (is_numeric($preStatus) && array_key_exists((int) $preStatus, $allowedStatuses)) { //preStatus is ID
            return (int) $preStatus;
        }

        //preStatus is code - return and fill statusId automatically
        if (in_array($preStatus, $allowedStatuses)) {
            return (int) array_flip($allowedStatuses)[$preStatus];
        }

        return null;
    }

    /**
     * Check if supplied POST status can be assigned to this eventId and if can, return correct postStatusId (transforming code to id)
     * (Check using event_type and so on)
     *
     * @param int $eventId
     * @param int $postStatus Either id or code
     * @return int|null Return correct statusId or null for invalid code
     */
    private function getPostStatusId(int $eventId, $postStatus): ?int
    {
        $allowedStatuses = $this->database->query("SELECT status.id, status.code FROM status "
                . "LEFT JOIN status_set ON status_set.id=status.status_set_id "
                . "LEFT JOIN event_types ON event_types.post_status_set_id=status_set.id "
                . "LEFT JOIN events ON events.event_type_id=event_types.id WHERE events.id=?", $eventId)->fetchPairs("id", "code");

        if (is_numeric($postStatus) && array_key_exists((int) $postStatus, $allowedStatuses)) { //postStatus is ID
            return (int) $postStatus;
        }

        //postStatus is code - return and fill statusId automatically
        if (in_array($postStatus, $allowedStatuses)) {
            return (int) array_flip($allowedStatuses)[$postStatus];
        }

        return null;
    }

    /**
     * Create row of attendance history
     *
     * @param int $userId
     * @param int $eventId
     * @param int $statusIdTo
     * @param string|null $preDescTo
     * @param int|null $statusIdFrom
     * @param string|null $preDescFrom
     * @return ActiveRow
     */
    private function createHistory(int $userId, int $eventId, int $statusIdTo, ?string $preDescTo = null, ?int $statusIdFrom = null, ?string $preDescFrom = null): ActiveRow
    {
        return $this->historyManager->createByArray([
            "updatedById" => $this->user->getId(),
            "updatedAt" => new DateTime(),
            "userId" => $userId,
            "eventId" => $eventId,
            "statusIdFrom" => $statusIdFrom,
            "preDescFrom" => $preDescFrom,
            "statusIdTo" => $statusIdTo,
            "preDescTo" => $preDescTo,
            "type" => History::TYPE_USER_ATTENDANCE_ENTRY,
        ]);
    }

    public function create(array $data, ?int $resourceId = null): BaseModel
    {
        if (empty($data)) {
            $this->respondBadRequest("No attendance entry provided");
        }

        if (!isset($data["userId"])) {
            $data["userId"] = $this->user->getId();
        }

        $this->checkInputs($data);

        $this->eventRow = $this->database->table(Event::TABLE)->where("id", $data["eventId"])->fetch();

        if (empty($this->eventRow)) {
            $this->responder->E4005_OBJECT_NOT_FOUND(Event::MODULE, $data["eventId"]);
        }

        $existingAttendance = $this->getByEventUserId($data["eventId"], $data["userId"]);

        $this->allowCreate($data); //allowCreate checks right for both creating and updating already created attendance
        if (!$existingAttendance) {
            $created = $this->createByArray($data);
            if ($created && isset($data["preStatusId"])) {
                $this->createHistory($data["userId"], $data["eventId"], $data["preStatusId"], $data["preDescription"] ?? null);
            }
        } else {
            $updated = $this->updateByArray($data["eventId"], $data);
            if ($updated && isset($data["preStatusId"])) {
                $this->createHistory($data["userId"], $data["eventId"], $data["preStatusId"], $data["preDescription"] ?? null, $existingAttendance->getPreStatusId(), $existingAttendance->getPreDescription());
            }
        }

        unset($this->myAttendances); //clear cache

        return $this->getByEventUserId($data["eventId"], $data["userId"]);
    }

    /**
     * Update table row based on given table, event_id and user_id and updates array. Function throws correct exception using class DBException
     * IDColumn can be changed if primary key is different than classic `id`
     *
     * @param string $table Table name
     * @param int $id ID
     * @param array $updates Array of updates
     * @param string $idColumn Caption of primary key column
     * @return int number of affected rows
     * @throws Exception
     */
    protected function updateRecord($table, $id, array $updates, $idColumn = "id")
    {
        try {
            $updated = $this->database->table($table)->where("event_id", $id)->where("user_id", $updates["user_id"])->update($updates);
        } catch (PDOException $exc) {
            throw DBException::from($exc, DBException::TYPE_UPDATE);
        }
        return $updated;
    }

    public function delete(int $resourceId, ?int $subResourceId = null): int
    {
        throw new NotImplementedException("Cannot delete attendance, once it been set");
    }

    public function read(int $resourceId, ?int $subResourceId = null): BaseModel
    {
        throw new NotImplementedException("Attendance can be read only along with event");
    }

    public function update(array $data, int $resourceId, ?int $subResourceId = null): BaseModel
    {
        throw new NotImplementedException("Update is performed only during POST request");
    }

    /**
     * Get my attendance on specific event id, using cache
     *
     * @param int $eventId
     * @return ActiveRow|null
     */
    public function getMyAttendance(int $eventId): ?ActiveRow
    {
        if (!isset($this->myAttendances)) {
            $this->myAttendances = $this->database->table($this->getTable())->where("user_id", $this->user->getId())->fetchPairs("event_id");
        }

        return $this->myAttendances[$eventId] ?? null;
    }
}
