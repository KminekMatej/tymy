<?php

namespace App\Presenters;

use Nette;
use App\Model;
use Nette\Application\UI\Form;
use Nette\Utils\Strings;

class EventPresenter extends SecuredPresenter {
        
    private $eventList;
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
        $this->setLevelCaptions(["0" => ["caption" => "Události", "link" => $this->link("Event:")]]);

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
    
    private function loadEventList($date = NULL, $direction = NULL) {
        $events = new \Tymy\Events($this->tapiAuthenticator, $this);
        $this->eventsFrom = date("Ym", strtotime("-6 months")) . "01";
        $this->eventsTo = date("Ym", strtotime("+6 months")) . "01";

        if ($direction == 1) {
            $this->eventsTo = date("Ym", strtotime("$date-01 +6 months")) . "01";
        } elseif ($direction == -1) {
            $this->eventsFrom = date("Ym", strtotime("$date-01 -6 months")) . "01";
        }

        $this->eventList = $events
                ->withMyAttendance(true)
                ->from($this->eventsFrom)
                ->to($this->eventsTo)
                ->order("startTime")
                ->fetch();
    }

    public function renderDefault() {
        if (!isset($this->eventList)) {
            $this->loadEventList(NULL, NULL);
        }
        
        $this->eventize();
        
        $this->template->agendaFrom = date("Y-m", strtotime($this->eventsFrom));
        $this->template->agendaTo = date("Y-m", strtotime($this->eventsTo));
        
        $this->template->currY = date("Y");
        $this->template->currM = date("m");
        $this->template->evMonths = $this->eventsMonthly;
        $this->template->events = $this->eventList;
        $this->template->eventTypes = $this->getEventTypes();
    }
    
    private function eventize() {
        $this->eventsJSObject = [];
        $this->eventsMonthly = [];
        foreach ($this->eventList as $ev) {
            $webName = \Nette\Utils\Strings::webalize($ev->caption);
            $this->eventsJSObject[] = (object)[
                    "id"=>$ev->id,
                    "title"=>$ev->caption,
                    "start"=>$ev->startTime,
                    "end"=>$ev->endTime,
                    "url"=>$this->link('event', array('udalost'=>$ev->id . "-$webName"))
                    ];
            
            $month = date("Y-m", strtotime($ev->startTime));
            $this->eventsMonthly[$month][] = $ev;
        }
    }

    private function getEventTypes($force = FALSE){
        $sessionSection = $this->getSession()->getSection("tymy");
        
        if(isset($sessionSection["eventTypes"]) && !$force)
            return $sessionSection["eventTypes"];
        
        $eventTypesObj = new \Tymy\EventTypes($this->tapiAuthenticator, $this);
        $eventTypesResult = $eventTypesObj->fetch();
        
        $eventTypes = [];
        foreach ($eventTypesResult as $type) {
            $eventTypes[$type->code] = $type;
            $preStatusSet = [];
            foreach ($type->preStatusSet as $preSS) {
                $preStatusSet[$preSS->code] = $preSS;
            }
            $eventTypes[$type->code]->preStatusSet = $preStatusSet;
            
            $postStatusSet = [];
            foreach ($type->postStatusSet as $postSS) {
                $postStatusSet[$postSS->code] = $postSS;
            }
            $eventTypes[$type->code]->postStatusSet = $postStatusSet;
        }
        $sessionSection["eventTypes"] = $eventTypes;
        return $eventTypes;
    }
    
    public function renderEvent($udalost) {
        $eventId = substr($udalost,0,strpos($udalost, "-"));
        $eventObj = new \Tymy\Event($this->tapiAuthenticator, $this);
        $event = $eventObj
                ->recId($eventId)
                ->fetch();
        
        $this->setLevelCaptions(["1" => ["caption" => $event->caption, "link" => $this->link("Event:event", $event->id . "-" . Strings::webalize($event->caption))]]);
        
        $usersObj = new \Tymy\Users($this->tapiAuthenticator, $this);
        $users = $usersObj
                ->fetch();
                
        $userArr = [];
        foreach ($users as $usr) {
            $userArr[$usr->id] = $usr;
        }
        
        //array keys are pre-set for sorting purposes
        $attArray = [];
        $attArray["YES"] = NULL;
        $attArray["LAT"] = NULL;
        $attArray["DKY"] = NULL;
        $attArray["NO"] = NULL;
        $attArray["UNKNOWN"] = NULL;
        
        foreach ($event->attendance as $attendee) {
            $user = $userArr[$attendee->userId];
            if($user->status != "PLAYER") continue; // display only players on event detail
            $gender = $user->gender;
            $user->preDescription = $attendee->preDescription;
            $attArray[$attendee->preStatus][$gender][$attendee->userId]=$user;
        }
        
        $event->allUsers = $attArray;
        
        $this->template->event = $event;
        $this->template->eventTypes = $this->getEventTypes();
    }
    
    public function handleAttendance($id, $code, $desc){
        $att = new \Tymy\Attendance($this->tapiAuthenticator, $this);
        $att->recId($id)
            ->preStatus($code)
            ->preDescription($desc)
            ->plan();
               
    }
    
    public function handleEventLoad($date = NULL, $direction = NULL) {
        $this->loadEventList($date, $direction);
        if ($this->isAjax()) {
            $this->eventize();
            $this->payload->events = $this->eventsJSObject;
            $this->redrawControl("events");
        }
    }

}
