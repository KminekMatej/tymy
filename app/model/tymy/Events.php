<?php

namespace Tymy;

use Nette;

/**
 * Description of Tymy
 *
 * @author matej
 */
final class Events extends Tymy{
    
    const TAPI_NAME = "events";
    
    private $dateFrom;
    private $order;
    private $dateTo;
    private $withMyAttendance = FALSE;
    public $eventsJSObject;
    public $eventsMonthly;
    public $eventsFrom;
    public $eventsTo;
    
    public function from($date){
        $this->dateFrom = $date;
        return $this;
    }
    
    public function to($date){
        $this->dateTo = $date;
        return $this;
    }
    
    public function withMyAttendance($withMyAttendance){
        $this->withMyAttendance = $withMyAttendance;
        return $this;
    }
    
    public function order($orderField){
        $this->order = $orderField;
        return $this;
    }
    
    public function select() {
        $url = "events";
        
        if($this->withMyAttendance){
            $url .= "/withMyAttendance";
        }
        $filter = [];
        
        if($this->dateFrom)
            $filter[] = "startTime>" . $this->dateFrom;
        if($this->dateTo)
            $filter[] = "startTime<" . $this->dateTo;
            
        if(count($filter)){
            $this->setUriParam("filter", join("~", $filter));
        }
        
        if($this->order){
            $this->setUriParam("order", $this->order);
        }
        
        $this->fullUrl .= $url;
        return $this;
    }
    
    protected function postProcess(){
        $data = $this->getData();
        
        $this->getResult()->menuWarningCount = 0;
        
        foreach ($data as $event) {
            $event->warning = false;
            if(property_exists($event, "myAttendance") && property_exists($event->myAttendance, "preStatus")){
                $eventClassMap = [
                    "YES" => "success",
                    "LAT" => "warning",
                    "NO" => "danger",
                    "DKY" => "danger",
                    "UNKNOWN" => "secondary",
                ];
                $event->preClass = $eventClassMap[$event->myAttendance->preStatus];
            } else {
                $event->warning = true;
                $this->getResult()->menuWarningCount++;
            }
            
            $this->timezone($event->closeTime);
            $this->timezone($event->startTime);
            $this->timezone($event->endTime);
            $event->webName = \Nette\Utils\Strings::webalize($event->caption);
            if($this->withMyAttendance){
                if(!property_exists($event, "myAttendance")) $event->myAttendance = new \stdClass ();
                if(!property_exists($event->myAttendance, "preStatus")) $event->myAttendance->preStatus = "UNKNOWN"; //set default value
                if(!property_exists($event->myAttendance, "preDescription")) $event->myAttendance->preDescription = ""; //set default value
                if(!property_exists($event->myAttendance, "postStatus")) $event->myAttendance->postStatus = "UNKNOWN"; //set default value
                if(!property_exists($event->myAttendance, "postDescription")) $event->myAttendance->postDescription = ""; //set default value
                if (property_exists($event->myAttendance, "preDatMod")) $this->timezone($event->myAttendance->preDatMod);
                if (property_exists($event->myAttendance, "postDatMod")) $this->timezone($event->myAttendance->postDatMod);
            } 
        }
    }
    
    public function loadYearEvents($date = NULL, $direction = NULL){
        $this->eventsFrom = date("Ym", strtotime("-6 months")) . "01";
        $this->eventsTo = date("Ym", strtotime("+6 months")) . "01";

        if ($direction == 1) {
            $this->eventsTo = date("Ym", strtotime("$date-01 +6 months")) . "01";
        } elseif ($direction == -1) {
            $this->eventsFrom = date("Ym", strtotime("$date-01 -6 months")) . "01";
        }
        $this->withMyAttendance(TRUE)
                ->from($this->eventsFrom)
                ->to($this->eventsTo)
                ->order("startTime");
        $this->urlStart();
        $this->select();
        $this->urlEnd();
        try {
            $this->result = $this->execute();
        } catch (\Tymy\Exception\APIAuthenticationException $exc) {
            $this->user->logout(true);
            $this->presenter->flashMessage('You have been signed out due to inactivity. Please sign in again.');
            $this->presenter->redirect('Sign:in', ['backlink' => $this->presenter->storeRequest()]);
        }
        $this->postProcess();
        $data = $this->getData();
        
        $this->eventsJSObject = [];
        $this->eventsMonthly = [];
        
        foreach ($data as $ev) {
            $eventColor = $this->calendarItemColor($ev);
            $eventProps = [
                "id" => $ev->id,
                "title" => $ev->caption,
                "start" => $ev->startTime,
                "end" => $ev->endTime,
                "url" => $this->presenter->link('Event:event', array('udalost' => $ev->id . "-" . $ev->webName))
            ];
            $this->eventsJSObject[] = (object)array_merge($eventProps, $eventColor);
            $month = date("Y-m", strtotime($ev->startTime));
            $this->eventsMonthly[$month][] = $ev;
        }
        return $this;
    }
    
    private function calendarItemColor($event) {
        $colorList = [
            "TRA" => '#5cb85c',
            "RUN" => '#0275d8',
            "MEE" => '#795548',
            "TOU" => '#f0ad4e',
            "CMP" => '#5bc0de',
            ];
        $eventColor = [];
        $invertColors = !property_exists($event, 'myAttendance') || !property_exists($event->myAttendance, 'preStatus');
        $eventColor["borderColor"] = $colorList[$event->type];
        $eventColor["backgroundColor"] = $invertColors ? 'white' : $colorList[$event->type];
        $eventColor["textColor"] = $invertColors ? $colorList[$event->type] : '';
        return $eventColor;
    }

}
