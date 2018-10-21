<?php

namespace App\Presenters;

use Tapi\EventDetailResource;
use Tapi\AttendanceConfirmResource;
use Tapi\AttendancePlanResource;
use Tapi\Exception\APIException;

class EventPresenter extends SecuredPresenter {

    /** @var EventDetailResource @inject */
    public $eventDetail;

    /** @var AttendanceConfirmResource @inject */
    public $attendanceConfirmer;

    /** @var AttendancePlanResource @inject */
    public $attendancePlanner;

    public function startup() {
        parent::startup();
        $this->setLevelCaptions(["1" => ["caption" => "Události", "link" => $this->link("Event:")]]);

        $this->template->addFilter('genderTranslate', function ($gender) {
            switch ($gender) {
                case "MALE": return "Muži";
                case "FEMALE": return "Ženy";
                case "UNKNOWN": return "Nezadáno";
            }
        });

        $this->template->addFilter("prestatusClass", function ($myPreStatus, $myPostStatus, $btn, $startTime) {
            $color = $this->supplier->getStatusClass($btn);
            if (strtotime($startTime) > strtotime(date("c")))// pokud podminka plati, akce je budouci
                return $btn == $myPreStatus ? "btn-outline-$color active" : "btn-outline-$color";
            else if ($myPostStatus == "not-set") // akce uz byla, post status nevyplnen
                return $btn == $myPreStatus && $myPreStatus != "not-set" ? "btn-outline-$color disabled active" : "btn-outline-secondary disabled";
            else
                return $btn == $myPostStatus && $myPostStatus != "not-set" ? "btn-outline-$color disabled active" : "btn-outline-secondary disabled";
        });
    }

    public function renderDefault($date = NULL, $direction = NULL) {
        parent::showNotes();
        try {
            $this->eventList->init()
                    ->setHalfYearFrom($date, $direction)
                    ->getData();
            $eventTypes = $this->eventTypeList->init()->getData();
        } catch (APIException $ex) {
            $this->handleTapiException($ex);
        }

        $months = $this->eventList->getAsMonthArray();
        foreach ($months as $eventMonth) {
            foreach ($eventMonth as $event) {
                $eventCaptions = $this->getEventCaptions($event, $eventTypes);
                $event->myPreStatusCaption = $eventCaptions["myPreStatusCaption"];
                $event->myPostStatusCaption = $eventCaptions["myPostStatusCaption"];
            }
        }
        $this->template->agendaFrom = date("Y-m", strtotime($this->eventList->getFrom()));
        $this->template->agendaTo = date("Y-m", strtotime($this->eventList->getTo()));
        $this->template->currY = date("Y");
        $this->template->currM = date("m");
        $this->template->evMonths = $this->eventList->getAsMonthArray();
        $this->template->events = $this->eventList->getAsArray();
        $this->template->eventTypes = $eventTypes;
        if ($this->isAjax()) {
            foreach ($this->eventList->getAsArray() as &$eventJs) {
                $eventJs->url = $this->link("Event:event", $eventJs->webName);
                unset($eventJs->webName);
                $eventJs->stick = true;
            }
            $this->payload->events = $this->eventList->getAsArray();
        }
    }

    public function renderEvent($udalost) {
        $this->template->cptNotDecidedYet = $this->translator->translate('event.notDecidedYet');
        $this->template->cptArrived = $this->translator->translate('event.arrived',2);
        $this->template->cptNotArrived = $this->translator->translate('event.notArrived',2);
        try {
            $event = $this->eventDetail->init()
                    ->setId($this->parseIdFromWebname($udalost))
                    ->getData();
            $eventTypes = $this->eventTypeList->init()->getData();
            $this->userList->init()->getData();
            $users = $this->userList->getById();
        } catch (APIException $ex) {
            $this->handleTapiException($ex);
        }
        parent::showNotes($event->id);
        
        $this->setLevelCaptions(["2" => ["caption" => $event->caption, "link" => $this->link("Event:event", $event->id . "-" . $event->webName)]]);

        //array keys are pre-set for sorting purposes
        $attArray = [];
        $attArray["POST"] = [];
        $attArray["POST"]["YES"] = [];
        $attArray["POST"]["NO"] = [];
        $attArray["PRE"] = [];
        $attArray["PRE"]["YES"] = [];
        $attArray["PRE"]["DKY"] = [];
        $attArray["PRE"]["LAT"] = [];
        $attArray["PRE"]["NO"] = [];
        $attArray["PRE"]["UNKNOWN"] = [];
        
        foreach ($event->attendance as $attendee) {
            $user = $users[$attendee->userId];
            if ($user->status != "PLAYER")
                continue; // display only players on event detail
            $gender = $user->gender;
            $user->preDescription = $attendee->preDescription;
            $mainKey = "PRE";
            $secondaryKey = $attendee->preStatus;
            if($attendee->postStatus != "UNKNOWN"){
                $mainKey = "POST";
                $secondaryKey = $attendee->postStatus;
            }
            if(!array_key_exists($secondaryKey, $attArray[$mainKey]))
                    $attArray[$mainKey][$secondaryKey] = [];
            if(!array_key_exists($gender, $attArray[$mainKey][$secondaryKey]))
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

    public function handleAttendance($id, $code, $desc) {
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
        }
    }
    
    public function handleAttendanceResult($id) {
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
        }
    }

    public function handleEventLoad() {
        $this->redrawControl("events-agenda");
    }

    private function getEventCaptions($event, $eventTypes) {
        return [
            "myPreStatusCaption" => $event->myAttendance->preStatus == "UNKNOWN" ? "not-set" : $eventTypes[$event->type]->preStatusSet[$event->myAttendance->preStatus]->code,
            "myPostStatusCaption" => $event->myAttendance->postStatus == "UNKNOWN" ? "not-set" : $eventTypes[$event->type]->postStatusSet[$event->myAttendance->postStatus]->code,
        ];
    }

}
