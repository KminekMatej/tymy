<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */
namespace Tymy\Module\Event\Presenter\Front;

use Tracy\Debugger;
use Tymy\Module\Attendance\Manager\HistoryManager;
use Tymy\Module\Attendance\Model\Attendance;
use Tymy\Module\Attendance\Model\Status;
use Tymy\Module\Attendance\Model\StatusSet;
use Tymy\Module\Event\Model\Event;
use Tymy\Module\User\Model\User;

/**
 * Description of DetailPresenter
 *
 * @author kminekmatej
 */
class DetailPresenter extends EventBasePresenter
{
    /** @inject */
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

        $this->setLevelCaptions(["2" => ["caption" => $event->getCaption(), "link" => $this->link(":Event:Detail:", $event->getId() . "-" . $event->getWebName())]]);

        $this->template->resultsClosed = false;

        $this->template->attendances = $this->loadEventAttendance($event);
        $this->template->event = $event;
        $this->template->eventTypes = $eventTypes;
        $this->template->eventType = $eventTypes[$event->getType()];
        $eventCaptions = $this->getEventCaptions($event, $eventTypes);
        $this->template->myPreStatusCaption = $eventCaptions["myPreStatusCaption"];
        $this->template->myPostStatusCaption = $eventCaptions["myPostStatusCaption"];
    }
    
    /**
     * Compose attendance array to be easily used on template
     * @param Event $event
     * @return array
     */
    private function loadEventAttendance(Event $event): array
    {
        $attendances = [];
        //return array, first key is attendance type (PRE, POST), then code (YES/NO/LAT), then gender (male/female/unknown), then user
        $usersWithAttendance = [];
        foreach ($event->getAttendance() as $attendance) {
            /* @var $attendance Attendance */
            $statusId = $attendance->getPostStatusId() ?: $attendance->getPreStatusId();
            $gender = $attendance->getUser()->getGender();

            if (!array_key_exists($statusId, $attendances)) {//init code
                $attendances[$statusId] = [];
            }
            if (!array_key_exists($gender, $attendances[$statusId])) {//init gender
                $attendances[$statusId][$gender] = [];
            }

            $attendances[$statusId][$gender][] = $attendance->getUser();
            $usersWithAttendance[] = $attendance->getUser()->getId();
        }

        //add all other players ad not decided yet
        $users = $this->userManager->getByStatus(User::STATUS_PLAYER);
        foreach ($users as $user) {
            if (in_array($user->getId(), $usersWithAttendance)) {
                continue;   //user has already filled its attendance
            }
            $attendances["unknown"][$user->getGender()][] = $user;
        }
        return $attendances;
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
