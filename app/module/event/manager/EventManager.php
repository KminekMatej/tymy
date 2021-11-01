<?php

namespace Tymy\Module\Event\Manager;

use Nette\Database\IRow;
use Nette\Database\Table\ActiveRow;
use Nette\Utils\DateTime;
use Nette\Utils\Strings;
use Tymy\Module\Attendance\Manager\AttendanceManager;
use Tymy\Module\Core\Factory\ManagerFactory;
use Tymy\Module\Core\Manager\BaseManager;
use Tymy\Module\Core\Model\BaseModel;
use Tymy\Module\Core\Model\Filter;
use Tymy\Module\Core\Model\Order;
use Tymy\Module\Event\Mapper\EventMapper;
use Tymy\Module\Event\Model\Event;
use Tymy\Module\Permission\Manager\PermissionManager;
use Tymy\Module\Permission\Model\Permission;
use Tymy\Module\Permission\Model\Privilege;
use Tymy\Module\User\Manager\UserManager;

/**
 * Description of EventManager
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 19. 9. 2020
 */
class EventManager extends BaseManager
{
    private PermissionManager $permissionManager;
    private AttendanceManager $attendanceManager;
    private UserManager $userManager;
    private DateTime $now;
    private ?Event $event = null;

    public function __construct(ManagerFactory $managerFactory, PermissionManager $permissionManager, UserManager $userManager, AttendanceManager $attendanceManager)
    {
        parent::__construct($managerFactory);
        $this->permissionManager = $permissionManager;
        $this->userManager = $userManager;
        $this->attendanceManager = $attendanceManager;
        $this->now = new DateTime();
    }

    /**
     * Maps one active row to object
     * @param ActiveRow|false $row
     * @param bool $force True to skip cache
     * @return Event|null
     */
    public function map(?IRow $row, bool $force = false): ?BaseModel
    {
        if (!$row) {
            return null;
        }

        /* @var $event Event */
        $event = parent::map($row, $force);

        $event->setInPast($row->in_past);
        $event->setInFuture($row->in_future);

        if (isset($row->user_id)) {
            $event->setMyAttendance($this->attendanceManager->map($row));
        } elseif ($event->getCloseTime() > new DateTime()) {   //my attendance doesnt exist and this event is still open
            $event->setAttendancePending(true);
        }

        $event->setWebName(Strings::webalize($event->getId() . "-" . $event->getCaption()));

        return $event;
    }

    protected function metaMap(BaseModel &$model, $userId = null): void
    {
        /* @var $model Event */
        $model->setCanView(empty($model->getViewRightName()) || $this->user->isAllowed($this->user->getId(), Privilege::USR($model->getViewRightName())));
        $model->setCanPlan(empty($model->getPlanRightName()) || $this->user->isAllowed($this->user->getId(), Privilege::USR($model->getPlanRightName())));
        $model->setCanResult(empty($model->getResultRightName()) ? $this->user->isAllowed($this->user->getId(), Privilege::SYS("EVE_ATT_UPDATE")) : $this->user->isAllowed($this->user->getId(), Privilege::USR($model->getResultRightName())));

        $colorList = $this->supplier->getEventColors();

        if (array_key_exists($model->getType(), $colorList)) {
            $invertColors = empty($model->getMyAttendance()) || empty($model->getMyAttendance()->getPreStatus());
            $model->setBackgroundColor($invertColors ? 'white' : $colorList[$model->getType()]);
            $model->setBorderColor($colorList[$model->getType()]);
            $model->setTextColor($invertColors ? $colorList[$model->getType()] : '');
        }
    }

    public function getById(int $id, bool $force = false): ?BaseModel
    {
        $query = "SELECT events.*, IF(events.start_time > NOW(),1,0) AS in_future, IF(events.start_time < NOW(),1,0) AS in_past, attendance.* FROM events LEFT JOIN attendance ON events.id=attendance.event_id AND attendance.user_id=? WHERE events.id=?";

        $eventRow = $this->database->query($query, $this->user->getId(), $id)->fetch();
        $event = $this->map($eventRow);
        $attendances = $this->attendanceManager->getByEvents([$event->getId()]);

        $event->setAttendance($attendances[$event->getId()] ?? []);

        return $event;
    }

    /**
     * Returns filter array, based on provided $from and $until datetimes
     * @param DateTime $from
     * @param DateTime $until
     * @return string Filter syntax string
     */
    private function getIntervalFilter(DateTime $from, DateTime $until): string
    {
        return join("~", [
            "startTime>" . $from->format(BaseModel::DATETIME_ISO_FORMAT),
            "startTime<" . $until->format(BaseModel::DATETIME_ISO_FORMAT),
        ]);
    }

    /**
     * Get array of event objects which user is allowed to view
     * @param int $userId
     * @return Event[]
     */
    public function getListUserAllowed(int $userId, ?string $filter = null, ?string $order = null, ?int $limit = null, ?int $offset = null)
    {
        $order = $order ?: "startTime__desc";
        $readPerms = $this->permissionManager->getUserAllowedPermissionNames($this->userManager->getById($userId), Permission::TYPE_USER);
        $params = [$userId];
        if (!empty($readPerms)) {
            $params[] = $readPerms;
        }

        $filters = $filter ? $this->filterToArray($filter) : [];

        $orders = $this->orderToArray($order);

        if (!empty($filters)) {
            $params = array_merge($params, Filter::toParams($filters));
        }

        $offset = $offset ?? 0;
        $limitQuery = $limit ? " LIMIT $offset,$limit" : "";

        $query = "SELECT events.*, "
                . "IF(events.start_time > NOW(),1,0) AS in_future, "
                . "IF(events.start_time < NOW(),1,0) AS in_past, "
                . "attendance.* FROM events "
                . "LEFT JOIN attendance ON events.id=attendance.event_id AND attendance.user_id=? "
                . "WHERE "
                . "(events.view_rights IS NULL OR events.view_rights = '' " . (empty($readPerms) ? "" : "OR events.view_rights IN (?) ") . ") "
                . Filter::toAndQuery($filters)    //add filtering sql
                . ($orders ? " ORDER BY " . Order::toString($orders) : "")
                . $limitQuery;

        $selector = $this->database->query($query, ...$params);
        $allRows = $selector->fetchAll();

        $eventIds = array_column($allRows, "id");
        $attendances = $this->attendanceManager->getByEvents($eventIds);

        $events = [];
        foreach ($allRows as $row) {
            /* @var $event Event */
            $event = $this->map($row);
            $event->setAttendance($attendances[$event->getId()] ?? []);
            $events[] = $event;
        }

        return $events;
    }
    
    public function getList(?array $idList = null, string $idField = "id", ?int $limit = null, ?int $offset = null): array
    {
        $offset = $offset ?? 0;
        $limitQuery = $limit ? " LIMIT $offset,$limit" : "";

        $selector = $this->database->query("SELECT events.*, "
                . "IF(events.start_time > NOW(),1,0) AS in_future, "
                . "IF(events.start_time < NOW(),1,0) AS in_past "
                . "FROM events "
                . "WHERE 1"
                . " ORDER BY events.start_time DESC "
                . $limitQuery);

        return $this->mapAll($selector->fetchAll());
    }

    /**
     * Get array of event ids which user is allowed to view
     * @param int $userId
     * @return int[]
     */
    public function getIdsUserAllowed($userId)
    {
        $readPerms = $this->permissionManager->getUserAllowedPermissionNames($this->userManager->getById($userId), Permission::TYPE_USER);
        return $this->database->table($this->getTable())->where("view_rights IS NULL OR view_rights = '' OR view_rights IN (?)", $readPerms)->order("start_time DESC")->fetchPairs(null, "id");
    }

    protected function allowCreate(?array &$data = null): void
    {
        if (!$this->user->isAllowed($this->user->getId(), Privilege::SYS("EVE_CREATE"))) {
            $this->respondForbidden();
        }

        if (!isset($data["endTime"])) {
            $data["endTime"] = $data["startTime"];
        }
        if (!isset($data["closeTime"])) {
            $data["closeTime"] = $data["closeTime"];
        }

        $closeTimeDT = new DateTime($data["closeTime"]);
        $startTimeDT = new DateTime($data["startTime"]);
        $endTimeDT = new DateTime($data["endTime"]);

        if ($closeTimeDT > $startTimeDT) {
            $this->respondBadRequest("Close time after start time");
        }
        if ($endTimeDT < $startTimeDT) {
            $this->respondBadRequest("Start time after end time");
        }
    }

    protected function allowDelete(?int $recordId): void
    {
        $this->event = $this->getById($recordId);

        if (!$this->user->isAllowed($this->user->getId(), Privilege::SYS("EVE_DELETE"))) {
            $this->respondForbidden();
        }
    }

    protected function allowRead(?int $recordId = null): void
    {
        if ($recordId) {
            $this->event = $this->getById($recordId);

            if (!$this->canRead($this->event, $this->user->getId())) {
                $this->responder->E4001_VIEW_NOT_PERMITTED(Event::MODULE, $recordId);
            }
        }
    }

    protected function allowUpdate(?int $recordId = null, ?array &$data = null): void
    {
        $this->event = $this->getById($recordId);

        if (!$this->canEdit($this->event, $this->user->getId())) {
            $this->respondForbidden();
        }
    }

    protected function getClassName(): string
    {
        return Event::class;
    }

    protected function getScheme(): array
    {
        return EventMapper::scheme();
    }

    /**
     * Check edit permission
     * @param Event $entity
     * @param int $userId
     * @return bool
     */
    public function canEdit($entity, $userId): bool
    {
        return in_array($userId, $this->userManager->getUserIdsWithPrivilege(Privilege::SYS("EVE_UPDATE")));
    }

    /**
     * Check read permission
     * @param Event $entity
     * @param int $userId
     * @return bool
     */
    public function canRead($entity, $userId): bool
    {
        return empty($entity->getViewRightName()) || in_array($entity->getViewRightName(), $this->permissionManager->getUserAllowedPermissionNames($this->userManager->getById($userId), Permission::TYPE_USER));
    }

    /**
     * Get user ids allowed to read given event
     * @param Event $record
     * @return int[]
     */
    public function getAllowedReaders(BaseModel $record): array
    {
        /* @var $record Event */
        return $this->userManager->getUserIdsWithPrivilege(Privilege::USR($record->getViewRightName()));
    }

    public function create(array $data, ?int $resourceId = null): BaseModel
    {
        $this->allowCreate($data);

        $createdRow = parent::createByArray($data);

        return $this->getById($createdRow->id);
    }

    public function delete(int $resourceId, ?int $subResourceId = null): int
    {
        $this->allowDelete($resourceId);

        $deleted = parent::deleteRecord($resourceId);

        return $deleted ? $resourceId : null;
    }

    public function read(int $resourceId, ?int $subResourceId = null): BaseModel
    {
        $this->allowRead($resourceId);

        return $this->event;
    }

    public function update(array $data, int $resourceId, ?int $subResourceId = null): BaseModel
    {
        $this->allowUpdate($resourceId, $data);

        parent::updateByArray($resourceId, $data);

        return $this->getById($resourceId);
    }

    /**
     * Return events specified by interval
     * 
     * @param int $userId
     * @param DateTime $from
     * @param DateTime $until
     * @return Event[]
     */
    public function getEventsInterval(int $userId, DateTime $from, DateTime $until)
    {
        return $this->getListUserAllowed($userId, $this->getIntervalFilter($from, $until));
    }

    /**
     * Get sum of all events with pending attendances
     * 
     * @param Event[] $events
     * @return int
     */
    public function getWarnings(array $events): int
    {
        $count = 0;
        foreach ($events as $event) {
            /* @var $event Event */
            if ($event->getAttendancePending()) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Get list of events, separated in array with year-month as key
     * 
     * @param array $events
     * @return array in the form of ["2021-01" => [...events...], "2021-02" => [...events...], ...]
     */
    public function getAsMonthArray(array $events)
    {
        $monthArray = [];

        foreach ($events as $event) {
            /* @var $event Event */
            $month = $event->getStartTime()->format(BaseModel::YEAR_MONTH);

            if (!array_key_exists($month, $monthArray)) {
                $monthArray[$month] = [];
            }

            $monthArray[$month][] = $event;
        }

        return $monthArray;
    }

    /**
     * Count all events
     * @return int
     */
    public function countAllEvents()
    {
        return $this->database->table(Event::TABLE)->count("id");
    }
}
