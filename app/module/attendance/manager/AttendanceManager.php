<?php

namespace Tymy\Module\Attendance\Manager;

use Exception;
use Nette\Database\IRow;
use Nette\Database\Table\ActiveRow;
use Nette\Utils\DateTime;
use PDOException;
use Tymy\Module\Attendance\Mapper\AttendanceMapper;
use Tymy\Module\Attendance\Model\Attendance;
use Tymy\Module\Attendance\Model\History;
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
        if (!$row) {
            return null;
        }

        /* @var $attendance Attendance */
        $attendance = parent::map($row, $force);

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
        $preEntered = isset($data["preStatus"]);
        $postEntered = isset($data["postStatus"]);

        if (!$preEntered && !$postEntered) {
            $this->respondBadRequest("Event pre or post status in attendance entry must be provided");
        }

        if ($preEntered) {
            $this->allowPreStatus($data["eventId"], $data["preStatus"]);
        }
        if ($postEntered) {
            $this->allowPostStatus($data["eventId"], $data["postStatus"]);
        }

        unset($data["preUserMod"]); //these values an be set only programatically
        unset($data["preDatMod"]);
        unset($data["postUserMod"]);
        unset($data["postDatMod"]);

        $now = new DateTime();
        if ($preEntered) {
            $data["preUserMod"] = $this->user->getId();
            $data["preDatMod"] = $now;
        }

        if ($postEntered) {
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
        } else {
            if (!$this->user->isAllowed($this->user->getId(), Privilege::SYS("EVE_ATT_UPDATE"))) {
                $this->respondForbidden();
            }
        }
    }

    /**
     * Check permissions if user, specified in data, can create attendance for this event
     *
     * @param array $data
     */
    private function allowAttend(array $data)
    {
        if ($this->user->getId() !== $data["userId"]) {
            if (!$this->user->isAllowed($this->user->getId(), Privilege::SYS("ATT_UPDATE"))) {
                $this->respondForbidden();
            }
        }

        $planRightName = $this->eventRow->plan_rights;
        if ($planRightName && !$this->user->isAllowed($this->user->getId(), Privilege::USR($planRightName))) {
            $this->respondForbidden();
        }
    }

    /**
     * Check if supplied PRE status can be assigned to this eventId
     * (Check using event_type and so on)
     *
     * @param int $eventId
     * @param string $preStatus
     * @return type
     */
    private function allowPreStatus(int $eventId, string $preStatus): bool
    {
        $allowedCodes = $this->database->query("SELECT statuses.id, statuses.code FROM statuses "
                        . "LEFT JOIN status_sets ON status_sets.id=statuses.set_id "
                        . "LEFT JOIN event_types ON event_types.pre_status_set=status_sets.id "
                        . "LEFT JOIN events ON events.type=event_types.code WHERE events.id=?", $eventId)->fetchPairs("id", "code");

        return is_array($allowedCodes) && in_array($preStatus, $allowedCodes);
    }

    /**
     * Check if supplied POST status can be assigned to this eventId
     * (Check using event_type and so on)
     *
     * @param int $eventId
     * @param string $postStatus
     * @return bool
     */
    private function allowPostStatus(int $eventId, string $postStatus): bool
    {
        $allowedCodes = $this->database->query("SELECT statuses.id, statuses.code FROM statuses "
                        . "LEFT JOIN status_sets ON status_sets.id=statuses.set_id "
                        . "LEFT JOIN event_types ON event_types.post_status_set=status_sets.id "
                        . "LEFT JOIN events ON events.type=event_types.code WHERE events.id=?", $eventId)->fetchPairs("id", "code");

        return is_array($allowedCodes) && in_array($postStatus, $allowedCodes);
    }

    private function createHistory(int $userId, int $eventId, string $preStatusTo, ?string $preDescTo = null, ?string $preStatusFrom = null, ?string $preDescFrom = null)
    {
        $this->historyManager->createByArray([
            "updatedById" => $this->user->getId(),
            "updatedAt" => new DateTime(),
            "userId" => $userId,
            "eventId" => $eventId,
            "preStatusFrom" => $preStatusFrom,
            "preDescFrom" => $preDescFrom,
            "preStatusTo" => $preStatusTo,
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
            if ($created && isset($data["preStatus"])) {
                $this->createHistory($data["userId"], $data["eventId"], $data["preStatus"], $data["preDescription"] ?? null);
            }
            return $this->getByEventUserId($data["eventId"], $data["userId"]);
        } else {
            $updated = $this->updateByArray($data["eventId"], $data);
            if ($updated && isset($data["preStatus"])) {
                $this->createHistory($data["userId"], $data["eventId"], $data["preStatus"], $data["preDescription"] ?? null, $existingAttendance->getPreStatus(), $existingAttendance->getPreDescription());
            }
            return $this->getByEventUserId($data["eventId"], $data["userId"]);
        }
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
        //cannot delete attendance, once it been set
    }

    public function read(int $resourceId, ?int $subResourceId = null): BaseModel
    {
        //attendance can be read only along with event
    }

    public function update(array $data, int $resourceId, ?int $subResourceId = null): BaseModel
    {
        //update si performed only during POST request
    }

    /**
     * Get my attendance on specific event id, using cache
     * 
     * @param int $eventId
     * @return ActiveRow|null
     */
    public function getUserAttendance(int $eventId): ?ActiveRow
    {
        if (!isset($this->myAttendances)) {
            $this->myAttendances = $this->database->table($this->getTable())->where("user_id", $this->user->getId())->fetchPairs("event_id");
        }

        return $this->myAttendances[$eventId] ?? null;
    }
}
