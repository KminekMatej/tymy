<?php

namespace Tymy\Module\Event\Presenter\Front;

use Nette\Utils\DateTime;
use Tymy\Module\Attendance\Manager\AttendanceManager;
use Tymy\Module\Attendance\Manager\HistoryManager;
use Tymy\Module\Attendance\Manager\StatusManager;
use Tymy\Module\Attendance\Model\Attendance;
use Tymy\Module\Attendance\Model\Status;
use Tymy\Module\Core\Model\BaseModel;
use Tymy\Module\Core\Presenter\Front\SecuredPresenter;
use Tymy\Module\Event\Manager\EventManager;
use Tymy\Module\Event\Manager\EventTypeManager;
use Tymy\Module\Event\Model\Event;
use Tymy\Module\User\Model\User;

class DefaultPresenter extends SecuredPresenter
{
    /** @inject */
    public EventManager $eventManager;

    /** @inject */
    public EventTypeManager $eventTypeManager;

    /** @inject */
    public StatusManager $statusManager;

    /** @inject */
    public AttendanceManager $attendanceManager;

    /** @inject */
    public HistoryManager $historyManager;

    public function startup()
    {
        parent::startup();
        $this->setLevelCaptions(["1" => ["caption" => $this->translator->translate("event.attendance", 2), "link" => $this->link(":Event:Default:")]]);

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
        /* @var $event Event */
        $event = $this->eventManager->getById($eventId);
        $eventTypes = $this->eventTypeManager->getIndexedList();
        $users = $this->userManager->getList();

        $this->setLevelCaptions(["2" => ["caption" => $event->getCaption(), "link" => $this->link("Event:event", $event->getId() . "-" . $event->getWebName())]]);

        //array keys are pre-set for sorting purposes
        $attArray = [];
        $attArray["POST"] = [];
        $attArray["POST"]["YES"] = [];
        $attArray["POST"]["NO"] = [];
        $attArray["PRE"] = [];
        foreach ($this->statusManager->getList() as $status) {
            /* @var $status Status */
            $attArray["PRE"][$status->getCode()] = [];
        }
        $attArray["PRE"]["UNKNOWN"] = [];

        $this->template->resultsClosed = false;
        foreach ($event->getAttendance() as $attendance) {
            /* @var $attendance Attendance */
            if (!array_key_exists($attendance->getUserId(), $users)) {
                continue;
            }

            if ($attendance->getPostStatus() !== null) {  //some attendance result has been aready filled, do not show buttons on default
                $this->template->resultsClosed = true;
            }

            /* @var $user User */
            $user = $users[$attendance->getUserId()];
            if ($user->getStatus() != User::STATUS_PLAYER) {
                continue; // display only players on event detail
            }

            $gender = $user->getGender();
            //$user->preDescription = $attendance->preDescription;
            $mainKey = "PRE";
            $secondaryKey = $attendance->getPreStatus();
            if ($attendance->getPostStatus() != "UNKNOWN") {
                $mainKey = "POST";
                $secondaryKey = $attendance->getPostStatus();
            }
            if (!array_key_exists($secondaryKey, $attArray[$mainKey])) {
                $attArray[$mainKey][$secondaryKey] = [];
            }
            if (!array_key_exists($gender, $attArray[$mainKey][$secondaryKey])) {
                $attArray[$mainKey][$secondaryKey][$gender] = [];
            }
            $attArray[$mainKey][$secondaryKey][$gender][$attendance->getUserId()] = $user;
        }

        $this->template->allUsers = $attArray;
        $this->template->event = $event;
        $this->template->eventTypes = $eventTypes;
        $this->template->eventType = $eventTypes[$event->getType()];
        $eventCaptions = $this->getEventCaptions($event, $eventTypes);
        $this->template->myPreStatusCaption = $eventCaptions["myPreStatusCaption"];
        $this->template->myPostStatusCaption = $eventCaptions["myPostStatusCaption"];
    }

    public function handleAttendance($id, $code, $desc)
    {
        $this->attendanceManager->create([
            "userId" => $this->user->getId(),
            "eventId" => $id,
            "preStatus" => $code,
            "preDescription" => $desc
        ]);

        if ($this->isAjax()) {
            $this->redrawControl("attendanceWarning");
            $this->redrawControl("attendanceTabs");
            $this->redrawNavbar();
        }
    }

    public function handleAttendanceResult($id)
    {
        $results = $this->getRequest()->getPost()["resultSet"];

        foreach ($results as $postStatusData) {
            $postStatusData["eventId"] = $id;
            $this->attendanceManager->create($postStatusData);
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
        $this->template->histories = $this->historyManager->getEventHistory($eventId);
        $this->template->emptyStatus = (object) ["code" => "", "caption" => "Nezadáno"];
    }
}