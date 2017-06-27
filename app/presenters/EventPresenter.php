<?php

namespace App\Presenters;

use Nette;
use App\Model;
use Nette\Application\UI\Form;
use Nette\Utils\Strings;

class EventPresenter extends SecuredPresenter {
        
    private $events;
    private $eventsFrom;
    private $eventsTo;
    private $eventsJSObject;
    private $eventsMonthly;
    
    public function __construct() {
        parent::__construct();
    }
    
    public function startup() {
        parent::startup();
        $this->getEventTypes();
        $this->setLevelCaptions(["1" => ["caption" => "Události", "link" => $this->link("Event:")]]);

        $this->template->addFilter('genderTranslate', function ($gender) {
            switch($gender){
                case "MALE": return "Muži";
                case "FEMALE": return "Ženy";
                case "UNKNOWN": return "Nezadáno";
            }
        });

        $this->template->addFilter("prestatusClass", function ($myPreStatus, $myPostStatus, $btn, $startTime) {
            $btnCls = [
                "LAT" => "warning",
                "NO" => "danger",
                "YES" => "success",
                "DKY" => "warning", // dont know yet
                ];
            $color = $btnCls[$btn];
            if(strtotime($startTime) > strtotime(date("c")))// pokud podminka plati, akce je budouci
                return $btn == $myPreStatus ? "btn-outline-$color active" : "btn-outline-$color";
            else if($myPostStatus == "not-set") // akce uz byla, post status nevyplnen
                return $btn == $myPreStatus && $myPreStatus != "not-set" ? "btn-outline-$color disabled active" : "btn-outline-secondary disabled";
            else 
                return $btn == $myPostStatus && $myPostStatus != "not-set" ? "btn-outline-$color disabled active" : "btn-outline-secondary disabled";
        });
    }

    public function renderDefault() {
        if(!$this->events){
            $eventsObj = new \Tymy\Events($this->tapiAuthenticator, $this);
            $this->events = $eventsObj->loadYearEvents(NULL, NULL);
        }
        
        $this->template->agendaFrom = date("Y-m", strtotime($this->events->eventsFrom));
        $this->template->agendaTo = date("Y-m", strtotime($this->events->eventsTo));
        $this->template->currY = date("Y");
        $this->template->currM = date("m");
        $this->template->evMonths = $this->events->eventsMonthly;
        $this->template->events = $this->events->eventsJSObject;
        $this->template->eventTypes = $this->getEventTypes();
    }
    
    public function renderEvent($udalost) {
        $eventId = substr($udalost,0,strpos($udalost, "-"));
        $eventObj = new \Tymy\Event($this->tapiAuthenticator, $this);
        $event = $eventObj
                ->recId($eventId)
                ->fetch();
        
        $this->setLevelCaptions(["2" => ["caption" => $event->caption, "link" => $this->link("Event:event", $event->id . "-" . $event->webName)]]);

        $users = $this->getUsers();
        
        //array keys are pre-set for sorting purposes
        $attArray = [];
        $attArray["YES"] = NULL;
        $attArray["LAT"] = NULL;
        $attArray["DKY"] = NULL;
        $attArray["NO"] = NULL;
        $attArray["UNKNOWN"] = NULL;
        
        foreach ($event->attendance as $attendee) {
            $user = $users->data[$attendee->userId];
            if($user->status != "PLAYER") continue; // display only players on event detail
            $gender = $user->gender;
            $user->preDescription = $attendee->preDescription;
            $attArray[$attendee->preStatus][$gender][$attendee->userId]=$user;
        }
        
        $event->allUsers = $attArray;
        $eventTypes = $this->getEventTypes();
        $this->template->event = $event;
        $this->template->eventTypes = $eventTypes;
        $this->template->myPreStatusCaption = $event->myAttendance->preStatus == "UNKNOWN" ? "not-set" : $eventTypes[$event->type]->preStatusSet[$event->myAttendance->preStatus]->code;
        $this->template->myPostStatusCaption = $event->myAttendance->postStatus == "UNKNOWN" ? "not-set" : $eventTypes[$event->type]->postStatusSet[$event->myAttendance->postStatus]->code;
        
    }
    
    public function handleAttendance($id, $code, $desc){
        $att = new \Tymy\Attendance($this->tapiAuthenticator, $this);
        $att->recId($id)
            ->preStatus($code)
            ->preDescription($desc)
            ->plan();
        if ($this->isAjax()) {
            $this->redrawControl("attendanceWarning");
            $this->redrawControl("attendanceTabs");
        }
    }
    
    public function handleEventLoad($date = NULL, $direction = NULL) {
        if ($this->isAjax()) {
            $eventsObj = new \Tymy\Events($this->tapiAuthenticator, $this);
            $this->events = $eventsObj->loadYearEvents($date, $direction);
            $this->payload->events = $this->events->eventsJSObject;
            $this->redrawControl("events");
        }
    }

}
