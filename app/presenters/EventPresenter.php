<?php

namespace Tymy\App\Presenters;

use Nette\Application\Responses\JsonResponse;
use Nette\Utils\DateTime;
use Tapi\Exception\APIException;
use Tymy\Module\Attendance\Manager\StatusManager;
use Tymy\Module\Attendance\Model\Attendance;
use Tymy\Module\Attendance\Model\Status;
use Tymy\Module\Core\Model\BaseModel;
use Tymy\Module\Event\Manager\EventManager;
use Tymy\Module\Event\Manager\EventTypeManager;
use Tymy\Module\Event\Model\Event;

class EventPresenter extends SecuredPresenter
{
    private $eventHistorian;
    private $attendanceConfirmer;
    private $attendancePlanner;

    /** @inject */
    public EventManager $eventManager;

    /** @inject */
    public EventTypeManager $eventTypeManager;

    /** @inject */
    public StatusManager $statusManager;

    public function startup()
    {
        parent::startup();
        $this->setLevelCaptions(["1" => ["caption" => $this->translator->translate("event.attendance", 2), "link" => $this->link("Event:")]]);

        $this->template->addFilter('genderTranslate', function ($gender) {
            switch ($gender) {
                case "MALE": return $this->translator->translate("team.male", 2);
                case "FEMALE": return $this->translator->translate("team.female", 2);
                case "UNKNOWN": return $this->translator->translate("team.unknownSex");
            }
        });

        $eventTypes = $this->eventTypeManager->getIndexedList();

        $this->template->addFilter("prestatusClass", function (?Attendance $myAttendance, $eventType, $code, $canPlan, $startTime) use ($eventTypes) {
            $myPreStatus = empty($myAttendance) || empty($myAttendance->getPreStatus()) || $myAttendance->getPreStatus() == "UNKNOWN" ? "not-set" : $eventTypes[$eventType]->getPreStatusSet()[$myAttendance->getPreStatus()]->getCode();
            $myPostStatus = empty($myAttendance) || empty($myAttendance->getPostStatus()) || $myAttendance->getPostStatus() == "UNKNOWN" ? "not-set" : $eventTypes[$eventType]->getPostStatusSet()[$myAttendance->getPostStatus()]->getCode();

            if (!$canPlan)
                return $code == $myPostStatus && $myPostStatus != "not-set" ? "attendance$code disabled active" : "btn-outline-secondary disabled";
            if (strtotime($startTime) > strtotime(date("c")))// pokud podminka plati, akce je budouci
                return $code == $myPreStatus ? "attendance$code active" : "attendance$code";
            else if ($myPostStatus == "not-set") // akce uz byla, post status nevyplnen
                return $code == $myPreStatus && $myPreStatus != "not-set" ? "attendance$code disabled active" : "btn-outline-secondary disabled";
            else
                return $code == $myPostStatus && $myPostStatus != "not-set" ? "attendance$code disabled active" : "btn-outline-secondary disabled";
        });

        $this->template->addFilter("statusColor", function (Status $status) {
            return $this->supplier->getStatusColor($status->getCode());
        });
    }

    public function beforeRender()
    {
        parent::beforeRender();
        $this->template->statusList = $this->statusManager->getList();
    }

    public function renderDefault($date = NULL, $direction = NULL)
    {
        $dateTimeBase = new DateTime();
        $dateTimeFrom = $dateTimeBase->modifyClone("- 6 months")->setTime(0, 0, 0);
        $dateTimeUntil = $dateTimeBase->modifyClone("+ 6 months")->setTime(23, 59, 59);

        if ($direction == 1) {
            $dateTimeUntil = (new DateTime($date))->modify("+ 6 months");
        } elseif ($direction == -1) {
            $dateTimeFrom = (new DateTime($date))->modify("- 6 months");
        }

        $events = $this->eventManager->getEventsInterval($this->user->getId(), $dateTimeFrom, $dateTimeUntil);

        $this->template->agendaFrom = $dateTimeFrom->format(BaseModel::YEAR_MONTH);
        $this->template->agendaTo = $dateTimeUntil->format(BaseModel::YEAR_MONTH);
        $this->template->currY = date("Y");
        $this->template->currM = date("m");
        $this->template->eventTypes = $this->eventTypeManager->getIndexedList();
        $this->template->events = $events;

        $this->template->evMonths = $this->eventManager->getAsMonthArray($events);

        if ($this->isAjax()) {
            $this->payload->events = $this->toFeed($events);
        }
    }

    public function renderEvent($udalost)
    {
        $this->template->cptNotDecidedYet = $this->translator->translate('event.notDecidedYet');
        $this->template->cptArrived = $this->translator->translate('event.arrived', 2);
        $this->template->cptNotArrived = $this->translator->translate('event.notArrived', 2);

        $eventId = $this->parseIdFromWebname($udalost);
        $event = $this->eventManager->getById($eventId);
        $eventTypes = $this->eventTypeManager->getList();
        $users = $this->userManager->getList();

        $this->setLevelCaptions(["2" => ["caption" => $event->caption, "link" => $this->link("Event:event", $event->id . "-" . $event->webName)]]);

        //array keys are pre-set for sorting purposes
        $attArray = [];
        $attArray["POST"] = [];
        $attArray["POST"]["YES"] = [];
        $attArray["POST"]["NO"] = [];
        $attArray["PRE"] = [];

        $this->options->allCodes = array_merge($this->options->allCodes, $statusSet->statusesByCode);

        $statusSet->statusesByCode[$status->code] = $status;

        foreach ($this->statusList->getStatusesByCode() as $status) {
            $attArray["PRE"][$status->code] = [];
        }
        $attArray["PRE"]["UNKNOWN"] = [];

        foreach ($event->attendance as $attendee) {
            if (!array_key_exists($attendee->userId, $users))
                continue;
            $user = $users[$attendee->userId];
            if ($user->status != "PLAYER")
                continue; // display only players on event detail
            $gender = $user->gender;
            $user->preDescription = $attendee->preDescription;
            $mainKey = "PRE";
            $secondaryKey = $attendee->preStatus;
            if ($attendee->postStatus != "UNKNOWN") {
                $mainKey = "POST";
                $secondaryKey = $attendee->postStatus;
            }
            if (!array_key_exists($secondaryKey, $attArray[$mainKey]))
                $attArray[$mainKey][$secondaryKey] = [];
            if (!array_key_exists($gender, $attArray[$mainKey][$secondaryKey]))
                $attArray[$mainKey][$secondaryKey][$gender] = [];

            $attArray[$mainKey][$secondaryKey][$gender][$attendee->userId] = $user;
        }

        $event->allUsers = $attArray;
        $this->template->event = $event;
        $this->template->eventTypes = $eventTypes;
        $eventCaptions = $this->getEventCaptions($event, $eventTypes);
        $this->template->myPreStatusCaption = $eventCaptions["myPreStatusCaption"];
        $this->template->myPostStatusCaption = $eventCaptions["myPostStatusCaption"];
    }

    public function actionFeed(string $start, string $end)
    {
        $events = $this->eventManager->getEventsInterval($this->user->getId(), new DateTime($start), new DateTime($end));

        $this->sendResponse(new JsonResponse($this->toFeed($events)));
    }

    /**
     * Transform array of events into event feed - array in format specified by FullCalendar specifications
     * 
     * @param Event[] $events
     * @return array
     */
    private function toFeed(array $events)
    {
        $feed = [];

        foreach ($events as $event) {
            /* @var $event Event */
            $feed[] = [
                "id" => $event->getId(),
                "title" => $event->getCaption(),
                "start" => $event->getStartTime()->format(BaseModel::DATETIME_ISO_FORMAT),
                "end" => $event->getEndTime()->format(BaseModel::DATETIME_ISO_FORMAT),
                "backgroundColor" => $event->getBackgroundColor(),
                "borderColor" => $event->getBorderColor(),
                "textColor" => $event->getTextColor(),
                "url" => $this->link("Event:event", $event->getWebName()),
            ];
        }

        return $feed;
    }

    public function handleAttendance($id, $code, $desc)
    {
        try {
            $this->attendancePlanner->init()
                    ->setId($id)
                    ->setPreStatus($code)
                    ->setPreDescription($desc)
                    ->perform();
        } catch (APIException $ex) {
            $this->handleTapiException($ex, "this");
        }
        if ($this->isAjax()) {
            $this->redrawControl("attendanceWarning");
            $this->redrawControl("attendanceTabs");
            $this->redrawNavbar();
        }
    }

    public function handleAttendanceResult($id)
    {
        $results = $this->getRequest()->getPost()["resultSet"];
        try {
            $this->attendanceConfirmer->init()
                    ->setId($id)
                    ->setPostStatuses($results)
                    ->perform();
        } catch (APIException $ex) {
            $this->handleTapiException($ex, "this");
        }
        if ($this->isAjax()) {
            $this->redrawControl("attendanceTabs");
            $this->redrawNavbar();
        }
    }

    public function handleEventLoad()
    {
        $this->redrawControl("events-agenda");
    }

    public function handleLoadHistory($udalost)
    {
        $eventId = $this->parseIdFromWebname($udalost);
        $this->loadEventHistory($eventId);
        $this->redrawControl("history");
        $this->redrawControl("historyBtn");
    }

    private function getEventCaptions($event, $eventTypes)
    {
        return [
            "myPreStatusCaption" => empty($event->myAttendance->preStatus) || $event->myAttendance->preStatus == "UNKNOWN" ? "not-set" : $eventTypes[$event->type]->preStatusSet[$event->myAttendance->preStatus]->code,
            "myPostStatusCaption" => empty($event->myAttendance->postStatus) || $event->myAttendance->postStatus == "UNKNOWN" ? "not-set" : $eventTypes[$event->type]->postStatusSet[$event->myAttendance->postStatus]->code,
        ];
    }

    private function loadEventHistory($eventId)
    {
        $histories = $this->eventHistorian->init()->setId($eventId)->getData();
        $this->template->emptyStatus = (object) ["code" => "", "caption" => "Nezadáno"];
        $this->template->histories = $histories;
    }
}