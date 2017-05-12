<?php

namespace App\Presenters;

use Nette;
use App\Model;
use Nette\Application\UI\Form;

class EventPresenter extends SecuredPresenter {
        
    public function __construct() {
        parent::__construct();
    }
    
    public function startup() {
        parent::startup();
        $this->getEventTypes();
        $this->setLevelCaptions(["0" => ["caption" => "Události", "link" => $this->link("Discussion:")]]);

        $this->template->addFilter('genderTranslate', function ($gender) {
            switch($gender){
                case "MALE": return "Muži";
                case "FEMALE": return "Ženy";
                case "UNKNOWN": return "Nezadáno";
            }
        });
    }
    
    public function renderDefault($date = NULL, $direction = NULL) {
        $events = new \Tymy\Events($this);
        $eventsFrom = date("Ym", strtotime("-3 months")) . "01";
        $eventsTo = date("Ym", strtotime("+3 months")) . "01";
        
        if($direction == 1){
            $eventsTo = date("Ym", strtotime("$date-01 +5 months")) . "01";
        } elseif($direction == -1){
            $eventsFrom = date("Ym", strtotime("$date-01 -5 months")) . "01";
        }
        
        $result = $events
                ->withMyAttendance(true)
                ->from($eventsFrom)
                ->to($eventsTo)
                ->fetch();
        $evJS = [];
        $evMonths = [];
        foreach ($result as $ev) {
            $webName = \Nette\Utils\Strings::webalize($ev->caption);
            $evObj = "{"
                    . "id:'".$ev->id."',"
                    . "title:'".$ev->caption."',"
                    . "start:'".$ev->startTime."',"
                    . "end:'".$ev->endTime."',"
                    . "url:'".$this->link('event', array('udalost'=>$ev->id . "-$webName")) ."'"
                    . "}";
            $month = date("Y-m", strtotime($ev->startTime));
            $evMonths[$month][] = $ev;
            $evJS[] = $evObj;
        }
        
        $this->template->agendaFrom = date("Y-m", strtotime($eventsFrom));
        $this->template->agendaTo = date("Y-m", strtotime($eventsTo));
        
        $this->template->currY = date("Y");
        $this->template->currM = date("m");
        $this->template->evJson = join(",", $evJS);
        $this->template->evMonths = $evMonths;
        $this->template->events = $result;
        $this->template->eventTypes = $this->getEventTypes();
        if($this->isAjax()){
            $events = [];
            foreach ($result as $ev) {
                $webName = \Nette\Utils\Strings::webalize($ev->caption);
                $events[] = (object)[
                    "id"=>$ev->id,
                    "title"=>$ev->caption,
                    "start"=>$ev->startTime,
                    "end"=>$ev->endTime,
                    "url"=>$this->link('event', array('udalost'=>$ev->id . "-$webName"))
                    ];
            }
            $this->payload->events = $events;
            $this->redrawControl("events");
        }
        
    }
    
    private function getEventTypes($force = FALSE){
        $sessionSection = $this->getSession()->getSection("tymy");
        
        if(isset($sessionSection["eventTypes"]) && !$force)
            return $sessionSection["eventTypes"];
        
        $eventTypesObj = new \Tymy\EventTypes($this);
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
        $eventObj = new \Tymy\Event($this);
        $event = $eventObj
                ->recId($eventId)
                ->fetch();
        
        $usersObj = new \Tymy\Users($this);
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
            $preStatus = isset($attendee->preStatus) ? $attendee->preStatus : "UNKNOWN";
            $g = isset($userArr[$attendee->userId]->gender) ? $userArr[$attendee->userId]->gender : "UNKNOWN";
            
            $userArr[$attendee->userId]->preDescription = isset($attendee->preDescription) ? $attendee->preDescription : "";
            $attArray[$preStatus][$g][$attendee->userId]=$userArr[$attendee->userId];
        }
        
        $event->allUsers = $attArray;
        
        $this->template->event = $event;
        $this->template->eventTypes = $this->getEventTypes();
    }
    
    function createComponentAttendanceRow() {
        return new \Nette\Application\UI\AttendanceRow($this->getUser()->getId(), $this->getSession()->getSection("tymy"));
    }

}
