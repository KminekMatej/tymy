<?php

namespace Tymy\Module\Event\Manager;

use Nette\Database\IRow;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;
use Nette\Utils\DateTime;
use Nette\Utils\Strings;
use Tymy\Module\Attendance\Manager\AttendanceManager;
use Tymy\Module\Attendance\Model\Attendance;
use Tymy\Module\Core\Factory\ManagerFactory;
use Tymy\Module\Core\Helper\ArrayHelper;
use Tymy\Module\Core\Manager\BaseManager;
use Tymy\Module\Core\Model\BaseModel;
use Tymy\Module\Core\Model\Filter;
use Tymy\Module\Core\Model\Order;
use Tymy\Module\Event\Mapper\EventMapper;
use Tymy\Module\Event\Model\Event;
use Tymy\Module\Event\Model\EventType;
use Tymy\Module\Permission\Manager\PermissionManager;
use Tymy\Module\Permission\Model\Permission;
use Tymy\Module\Permission\Model\Privilege;
use Tymy\Module\PushNotification\Manager\NotificationGenerator;
use Tymy\Module\PushNotification\Manager\PushNotificationManager;
use Tymy\Module\User\Manager\UserManager;

/**
 * Description of EventManager
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 19. 9. 2020
 */
class EventManager extends BaseManager
{
    public const EVENTS_PER_PAGE = 15;
    private DateTime $now;
    private ?Event $event = null;

    public function __construct(ManagerFactory $managerFactory, private PermissionManager $permissionManager, private UserManager $userManager, private AttendanceManager $attendanceManager, private EventTypeManager $eventTypeManager, private NotificationGenerator $notificationGenerator, private PushNotificationManager $pushNotificationManager)
    {
        parent::__construct($managerFactory);
        $this->now = new DateTime();
    }

    /**
     * Maps one active row to object
     * @param IRow|null $row
     * @param bool $force True to skip cache
     * @return Event|null
     */
    public function map(?IRow $row, bool $force = false): ?BaseModel
    {
        if ($row === null) {
            return null;
        }

        /* @var $event Event */
        $event = parent::map($row, $force);

        /* @var $eventType EventType */
        $eventType = $this->eventTypeManager->map($row->ref(EventType::TABLE, "event_type_id"));
        $event->setEventType($eventType);
        $event->setType($eventType->getCode());

        $event->setInPast($row->start_time < $this->now);
        $event->setInFuture($row->start_time > $this->now);

        $event->setWebName(Strings::webalize($event->getId() . "-" . $event->getCaption()));

        return $event;
    }

    protected function metaMap(BaseModel &$model, $userId = null): void
    {
        /* @var $model Event */
        $model->setCanView(empty($model->getViewRightName()) || $this->user->isAllowed($this->user->getId(), Privilege::USR($model->getViewRightName())));
        $model->setCanPlan(empty($model->getPlanRightName()) || $this->user->isAllowed($this->user->getId(), Privilege::USR($model->getPlanRightName())));
        $model->setCanPlanOthers($this->user->isAllowed($this->user->getId(), Privilege::SYS("ATT_UPDATE")));
        $model->setCanResult(empty($model->getResultRightName()) ? $this->user->isAllowed($this->user->getId(), Privilege::SYS("EVE_ATT_UPDATE")) : $this->user->isAllowed($this->user->getId(), Privilege::USR($model->getResultRightName())));

        $eventColor = '#' . $this->eventTypeManager->getEventTypeColor($model->getEventTypeId());

        $myAttendance = $this->attendanceManager->getMyAttendance($model->getId());

        if ($myAttendance !== null) {
            $model->setMyAttendance($this->attendanceManager->map($myAttendance));
        } elseif ($model->getCloseTime() > $this->now) { //my attendance doesnt exist and this event is still open
            $model->setAttendancePending(true);
        }

        $invertColors = !$myAttendance || empty($myAttendance->pre_status_id);
        $model->setBackgroundColor($invertColors ? 'white' : $eventColor);
        $model->setBorderColor($eventColor);
        $model->setTextColor($invertColors ? $eventColor : '');
    }

    public function getById(int $id, bool $force = false): ?BaseModel
    {
        $event = parent::getById($id);

        if (!$event instanceof \Tymy\Module\Core\Model\BaseModel) {
            return null;
        }

        $this->addAttendancesToEvent($event);

        return $event;
    }

    /**
     * Returns filter array, based on provided $from and $until datetimes
     * @return string Filter syntax string
     */
    private function getIntervalFilter(DateTime $from, DateTime $until): string
    {
        return implode("~", [
            "startTime>" . $from->format(BaseModel::DATETIME_ISO_FORMAT),
            "startTime<" . $until->format(BaseModel::DATETIME_ISO_FORMAT),
        ]);
    }

    /**
    * Get year events for event report. Also autodetects page to show if no page is specified
    *
    * @return array [
     "page" => (int),    //number of current page. Either supplied or auto detected
     "totalCount" => (int), //total number of events for given year (needed for pagination script)
     "events" => array , //Event[]
     ]
    */
    public function getYearEvents(int $userId, int $year, ?int $page = null): array
    {
        $yearEventsSelector = $this->selectUserEvents($userId)->where("start_time LIKE ?", "$year-%");

        if (!$page) {//if page is not set, we should autodetect proper page according to current date
            $eventsBeforeToday = (clone $yearEventsSelector)
                ->where("start_time < ?", new DateTime())
                ->count("id");

            $page = (int) floor($eventsBeforeToday / EventManager::EVENTS_PER_PAGE) + 1;
        }

        $totalCount = (clone $yearEventsSelector)->count("id");
        $lastDates = $this->selectUserEvents($userId)->select("MAX(start_time) AS latestDate, MIN(start_time) AS lowestDate")->fetch();

        $offset = ($page - 1) * EventManager::EVENTS_PER_PAGE;

        $events = $this->mapAll($yearEventsSelector->order("start_time ASC")->limit(EventManager::EVENTS_PER_PAGE, $offset)->fetchAll());
        $this->addAttendances($events);

        return [
            "page" => $page,
            "totalCount" => $totalCount,
            "firstYear" => $lastDates->lowestDate ? $lastDates->lowestDate->format("Y") : date("Y"),
            "lastYear" => $lastDates->latestDate ? $lastDates->latestDate->format("Y") : date("Y"),
            "lastPage" => ceil($totalCount / EventManager::EVENTS_PER_PAGE),
            "events" => $events,
        ];
    }

    /**
     * Get array of event objects which user is allowed to view
     * @return Event[]
     */
    public function getListUserAllowed(int $userId, ?string $filter = null, ?string $order = null, ?int $limit = null, ?int $offset = null): array
    {
        $selector = $this->selectUserEvents($userId);

        if ($filter) {
            Filter::addFilter($selector, $this->filterToArray($filter));
        }

        $selector->order(Order::toString($this->orderToArray($order ?: "startTime__desc")))
            ->limit($limit ?: 200, $offset ?: 0);

        $events = $this->mapAll($selector->fetchAll());

        $this->addAttendances($events);

        return $events;
    }

    /**
     * Load events of user in specified interval and where users attendance is one of selected
     * @return ActiveRow[]
     */
    public function getEventsOfPrestatus(int $userId, array $prestatusIds, DateTime $since): array
    {
        $selector = $this->selectUserEvents($userId)
            ->select($this->getTable() . ".*")
            ->select(":" . Attendance::TABLE . "(event).pre_status_id")
            ->joinWhere(":" . Attendance::TABLE . "(event)", ":" . Attendance::TABLE . "(event).user_id = ?", $userId)
            ->where("end_time > ?", $since) //do not load older events than since
            ->where(":" . Attendance::TABLE . "(event).pre_status_id IN (?) OR :" . Attendance::TABLE . "(event).pre_status_id IS NULL", $prestatusIds)
            ->order("start_time DESC")->group($this->getTable() . ".id");

        return $selector->fetchAll();
    }

    /**
     * Get currently active events
     * @return Event[]
     */
    public function getCurrentEvents(int $userId): array
    {
        $selector = $this->selectUserEvents($userId)
            ->where("start_time < NOW()") //has already started
            ->where("end_time > NOW()") //but has not already ended
            ->order(Order::toString($this->orderToArray("startTime__desc")))
            ->limit(5, 0);

        $events = $this->mapAll($selector->fetchAll());

        $this->addAttendances($events);

        return $events;
    }

    /**
     * Load attendances from database and automatically adds all of them to input array of events
     */
    private function addAttendances(array &$events): void
    {
        $eventIds = ArrayHelper::entityIds($events);
        $attendances = $this->attendanceManager->getByEvents($eventIds);
        $allSimpleUsers = $this->userManager->getSimpleUsers();

        foreach ($events as &$event) {
            $this->addAttendancesToEvent($event, $attendances[$event->getId()] ?? [], $allSimpleUsers);
        }
    }

    /**
     * Add attendance to one event
     * @param array|null $eventAttendances Cached attendances - null loads them from database for just this one event
     * @param array|null $allSimpleUsers Cached all simple users, null loads them from database
     */
    private function addAttendancesToEvent(Event $event, ?array $eventAttendances = null, ?array $allSimpleUsers = null): void
    {
        if ($eventAttendances == null) {
            $eventAttendances = $this->attendanceManager->getByEvents([$event->getId()])[$event->getId()] ?? [];
        }

        if ($allSimpleUsers == null) {
            $allSimpleUsers = $this->userManager->getSimpleUsers();
        }
        $allUserIds = array_keys($allSimpleUsers);

        /* @var $event Event */
        $event->setAttendance($eventAttendances);

        //now add attendances to all users that doesnt have any attendance
        $remainingUserIds = array_diff($allUserIds, array_keys($eventAttendances));
        foreach ($remainingUserIds as $remainingUserId) {
            $event->addAttendance(
                (new Attendance())
                    ->setEventId($event->getId())
                    ->setUserId($remainingUserId)
                    ->setUser($allSimpleUsers[$remainingUserId])
            );
        }
    }

    /**
     * Get basic selector for user permitted events
     */
    private function selectUserEvents(int $userId): Selection
    {
        $readPerms = $this->permissionManager->getUserAllowedPermissionNames($this->userManager->getById($userId), Permission::TYPE_USER);

        $readPermsQ = ["`view_rights` IS NULL", "`view_rights` = ''"];
        if (!empty($readPerms)) {
            $readPermsQ[] = "`view_rights` IN (?)";
        }

        return $this->database->table($this->getTable())
                ->where(implode(" OR ", $readPermsQ), empty($readPerms) ? null : $readPerms);
    }

    /**
     * Get array of event ids which user is allowed to view
     * @return int[]
     */
    public function getIdsUserAllowed(int $userId): array
    {
        return $this->selectUserEvents($userId)->fetchPairs(null, "id");
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

        //if there is no `eventTypeId` supplied, load it from `type` input
        if (!isset($data["eventTypeId"])) {
            if (!isset($data["type"])) {
                $this->responder->E4013_MISSING_INPUT("eventTypeId");
            }

            $code = $this->eventTypeManager->getByCode($data["type"]);

            if (empty($code)) {
                $this->respondNotFound("Event type", $data["type"]);
            }

            $data["eventTypeId"] = $code->id;
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

        //if there is no `eventTypeId` supplied, load it from `type` input
        if (isset($data["type"]) && $data["type"] !== $this->event->getType()) { //changing event type
            $code = $this->eventTypeManager->getByCode($data["type"]);

            if (empty($code)) {
                $this->respondNotFound("Event type", $data["type"]);
            }

            $data["eventTypeId"] = $code->id;
        }
    }

    protected function getClassName(): string
    {
        return Event::class;
    }

    /**
     * @return \Tymy\Module\Core\Model\Field[]
     */
    protected function getScheme(): array
    {
        return EventMapper::scheme();
    }

    /**
     * Check edit permission
     * @param Event $entity
     */
    public function canEdit($entity, int $userId): bool
    {
        return in_array($userId, $this->userManager->getUserIdsWithPrivilege(Privilege::SYS("EVE_UPDATE")));
    }

    /**
     * Check read permission
     * @param Event $entity
     */
    public function canRead($entity, int $userId): bool
    {
        return empty($entity->getViewRightName()) || in_array($entity->getViewRightName(), $this->permissionManager->getUserAllowedPermissionNames($this->userManager->getById($userId), Permission::TYPE_USER));
    }

    /**
     * Get user ids allowed to read given event
     * @param Event $record
     * @return mixed[]|int[]
     */
    public function getAllowedReaders(BaseModel $record): array
    {
        /* @var $record Event */
        return $record->getViewRightName() ?
            $this->userManager->getUserIdsWithPrivilege(Privilege::USR($record->getViewRightName())) :
            $this->getAllUserIds();
    }

    public function create(array $data, ?int $resourceId = null): BaseModel
    {
        $this->allowCreate($data);

        $createdRow = parent::createByArray($data);
        $createdEvent = $this->getById($createdRow->id);

        $notification = $this->notificationGenerator->createEvent($createdEvent);
        $this->pushNotificationManager->notifyUsers($notification, $this->getAllowedReaders($createdEvent));

        return $createdEvent;
    }

    public function delete(int $resourceId, ?int $subResourceId = null): int
    {
        $this->allowDelete($resourceId);

        $deleted = parent::deleteRecord($resourceId);

        $notification = $this->notificationGenerator->deleteEvent($this->event);
        $this->pushNotificationManager->notifyUsers($notification, $this->getAllowedReaders($this->event));

        return $deleted !== 0 ? $resourceId : null;
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

        $oldStartTime = $this->event->getStartTime();

        $this->event = $this->getById($resourceId);

        if ($this->event->getStartTime()->format(BaseModel::DATETIME_ENG_FORMAT) !== $oldStartTime->format(BaseModel::DATETIME_ENG_FORMAT)) {
            $notification = $this->notificationGenerator->changeEventTime($this->event, $this->event->getStartTime());
            $this->pushNotificationManager->notifyUsers($notification, $this->getAllowedReaders($this->event));
        }

        return $this->event;
    }

    /**
     * Return events specified by interval
     *
     * @return Event[]
     */
    public function getEventsInterval(int $userId, DateTime $from, DateTime $until, ?string $order = null): array
    {
        return $this->getListUserAllowed($userId, $this->getIntervalFilter($from, $until), $order);
    }

    /**
     * Get sum of all events with pending attendances
     *
     * @param Event[] $events
     */
    public function getWarnings(array $events): int
    {
        $count = 0;
        foreach ($events as $event) {
            /* @var $event Event */
            if ($event->getAttendancePending() && $event->getCanPlan()) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Get list of events, separated in array with year-month as key
     *
     * @return array in the form of ["2021-01" => [...events...], "2021-02" => [...events...], ...]
     */
    public function getAsMonthArray(array $events): array
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
     */
    public function countAllEvents(): int
    {
        return $this->database->table(Event::TABLE)->count("id");
    }
}
