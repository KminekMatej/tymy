<?php

namespace Tymy;

use Nette;

/**
 * Description of Tymy
 *
 * @author matej
 */
final class Events extends Tymy{
    
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
            if(property_exists($event, "myAttendance") && property_exists($event->myAttendance, "preStatus")){
                switch ($event->myAttendance->preStatus) {
                    case "YES":
                        $event->preClass = "success";
                        break;
                    case "LAT":
                        $event->preClass = "warning";
                        break;
                    case "NO":
                        $event->preClass = "danger";
                        break;
                    case "DKY":
                        $event->preClass = "danger";
                        break;
                    case "UNKNOWN":
                        $event->preClass = "secondary";
                        break;

                    default:
                        break;
                }
            } else {
                $this->getResult()->menuWarningCount++;
            }
            
            $this->timezone($event->closeTime);
            $this->timezone($event->startTime);
            $this->timezone($event->endTime);
            $event->webName = \Nette\Utils\Strings::webalize($event->caption);
            if($this->withMyAttendance)
                $this->timezone($event->myAttendance->preDatMod);
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
            $this->eventsJSObject[] = (object)[
                    "id"=>$ev->id,
                    "title"=>$ev->caption,
                    "start"=>$ev->startTime,
                    "end"=>$ev->endTime,
                    "url"=>$this->presenter->link('event', array('udalost'=>$ev->id . "-" . $ev->webName))
                    ];
            
            $month = date("Y-m", strtotime($ev->startTime));
            $this->eventsMonthly[$month][] = $ev;
        }
        
        return $this;
    }
}
