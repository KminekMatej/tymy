<?php

namespace Tymy\App\Presenters;

use Tapi\Exception\APIException;
use Tymy\Module\Event\Manager\EventManager;

class EventPresenter extends SecuredPresenter {
    public $eventDetail;

    public $eventHistorian;
    public $attendanceConfirmer;
    public $attendancePlanner;
    
    public EventManager $eventManager;

    public function startup() {
        parent::startup();
        $this->setLevelCaptions(["1" => ["caption" => $this->translator->translate("event.attendance", 2), "link" => $this->link("Event:")]]);

        $this->template->addFilter('genderTranslate', function ($gender) {
            switch ($gender) {
                case "MALE": return $this->translator->translate("team.male", 2);
                case "FEMALE": return $this->translator->translate("team.female", 2);
                case "UNKNOWN": return $this->translator->translate("team.unknownSex");
            }
        });

        $this->template->addFilter("prestatusClass", function ($myPreStatus, $myPostStatus, $code, $canPlan, $startTime) {
            if(!$canPlan)
                return $code == $myPostStatus && $myPostStatus != "not-set" ? "attendance$code disabled active" : "btn-outline-secondary disabled";
            if (strtotime($startTime) > strtotime(date("c")))// pokud podminka plati, akce je budouci
                return $code == $myPreStatus ? "attendance$code active" : "attendance$code";
            else if ($myPostStatus == "not-set") // akce uz byla, post status nevyplnen
                return $code == $myPreStatus && $myPreStatus != "not-set" ? "attendance$code disabled active" : "btn-outline-secondary disabled";
            else
                return $code == $myPostStatus && $myPostStatus != "not-set" ? "attendance$code disabled active" : "btn-outline-secondary disabled";
        });
    }

    public function beforeRender(){
        parent::beforeRender();
        $this->statusList->init()->perform();
        $this->template->statusList = $this->statusList->getStatusesByCode();
    }
        
    
    public function renderDefault($date = NULL, $direction = NULL) {
        //parent::showNotes();
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
        $this->template->events = $this->eventList->getAsArray();
        $this->template->evMonths = $this->eventList->getAsMonthArray();
        $this->template->eventTypes = $eventTypes;
        if ($this->isAjax()) {
            $this->payload->events = $this->eventList->getAsArray();
        }
    }

    public function renderEvent($udalost) {
        $this->template->cptNotDecidedYet = $this->translator->translate('event.notDecidedYet');
        $this->template->cptArrived = $this->translator->translate('event.arrived',2);
        $this->template->cptNotArrived = $this->translator->translate('event.notArrived',2);
        try {
            $eventId = $this->parseIdFromWebname($udalost);
            $event = $this->eventDetail->init()
                    ->setId($eventId)
                    ->getData();
            $eventTypes = $this->eventTypeList->init()->getData();
            $this->userList->init()->getData();
            $users = $this->userList->getById();
        } catch (APIException $ex) {
            $this->handleTapiException($ex);
        }
        //parent::showNotes($event->id);
        
        $this->setLevelCaptions(["2" => ["caption" => $event->caption, "link" => $this->link("Event:event", $event->id . "-" . $event->webName)]]);

        //array keys are pre-set for sorting purposes
        $attArray = [];
        $attArray["POST"] = [];
        $attArray["POST"]["YES"] = [];
        $attArray["POST"]["NO"] = [];
        $attArray["PRE"] = [];
        foreach ($this->statusList->getStatusesByCode() as $status) {
            $attArray["PRE"][$status->code] = [];
        }
        $attArray["PRE"]["UNKNOWN"] = [];
        
        foreach ($event->attendance as $attendee) {
            if(!array_key_exists($attendee->userId, $users))
                continue;
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
    
    public function actionFeed(string $start, string $end)
    {
        $events = $this->eventManager->getEventsInterval($this->user->getId(), new \Nette\Utils\DateTime($start), new \Nette\Utils\DateTime($end));
        \Tracy\Debugger::barDump($events);
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
            $this->redrawNavbar();
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
            $this->redrawNavbar();
        }
    }

    public function handleEventLoad() {
        $this->redrawControl("events-agenda");
    }

    public function handleLoadHistory($udalost){
        $eventId = $this->parseIdFromWebname($udalost);
        $this->loadEventHistory($eventId);
        $this->redrawControl("history");
        $this->redrawControl("historyBtn");
    }
    
    private function getEventCaptions($event, $eventTypes) {
        return [
            "myPreStatusCaption" => empty($event->myAttendance->preStatus) || $event->myAttendance->preStatus == "UNKNOWN" ? "not-set" : $eventTypes[$event->type]->preStatusSet[$event->myAttendance->preStatus]->code,
            "myPostStatusCaption" => empty($event->myAttendance->postStatus) || $event->myAttendance->postStatus == "UNKNOWN" ? "not-set" : $eventTypes[$event->type]->postStatusSet[$event->myAttendance->postStatus]->code,
        ];
    }

    private function loadEventHistory($eventId){
        $histories = $this->eventHistorian->init()->setId($eventId)->getData();
        $this->template->emptyStatus = (object)["code" => "", "caption" => "Nezadáno"];
        $this->template->histories = $histories;
    }
}
