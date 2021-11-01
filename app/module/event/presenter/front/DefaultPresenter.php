<?php

namespace Tymy\Module\Event\Presenter\Front;

use Nette\Utils\DateTime;
use Tymy\Module\Attendance\Manager\AttendanceManager;
use Tymy\Module\Attendance\Manager\HistoryManager;
use Tymy\Module\Core\Model\BaseModel;

class DefaultPresenter extends EventBasePresenter
{

    /** @inject */
    public AttendanceManager $attendanceManager;

    /** @inject */
    public HistoryManager $historyManager;


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