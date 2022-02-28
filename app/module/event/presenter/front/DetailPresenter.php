<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */
namespace Tymy\Module\Event\Presenter\Front;

use Tymy\Module\Attendance\Manager\HistoryManager;
use Tymy\Module\Attendance\Model\Attendance;
use Tymy\Module\Attendance\Model\Status;
use Tymy\Module\Event\Model\Event;
use Tymy\Module\User\Model\User;

/**
 * Description of DetailPresenter
 *
 * @author kminekmatej
 */
class DetailPresenter extends EventBasePresenter
{
    /* @inject */
    public HistoryManager $historyManager;

    public function renderDefault(string $resource)
    {
        $this->template->cptNotDecidedYet = $this->translator->translate('event.notDecidedYet');
        $this->template->cptArrived = $this->translator->translate('event.arrived', 2);
        $this->template->cptNotArrived = $this->translator->translate('event.notArrived', 2);

        $eventId = $this->parseIdFromWebname($resource);
        /* @var $event Event */
        $event = $this->eventManager->getById($eventId);
        $eventTypes = $this->eventTypeManager->getIndexedList();
        $users = $this->userManager->getList();

        $this->setLevelCaptions(["2" => ["caption" => $event->getCaption(), "link" => $this->link(":Event:Detail:", $event->getId() . "-" . $event->getWebName())]]);

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

    private function getEventCaptions($event, $eventTypes)
    {
        return [
            "myPreStatusCaption" => empty($event->myAttendance->preStatus) || $event->myAttendance->preStatus == "UNKNOWN" ? "not-set" : $eventTypes[$event->type]->preStatusSet[$event->myAttendance->preStatus]->code,
            "myPostStatusCaption" => empty($event->myAttendance->postStatus) || $event->myAttendance->postStatus == "UNKNOWN" ? "not-set" : $eventTypes[$event->type]->postStatusSet[$event->myAttendance->postStatus]->code,
        ];
    }

    public function handleLoadHistory($udalost)
    {
        $eventId = $this->parseIdFromWebname($udalost);
        $this->loadEventHistory($eventId);
        $this->redrawControl("history");
        $this->redrawControl("historyBtn");
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

    private function loadEventHistory($eventId)
    {
        $this->template->histories = $this->historyManager->getEventHistory($eventId);
        $this->template->emptyStatus = (object) ["code" => "", "caption" => "Nezadáno"];
    }
}
