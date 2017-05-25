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
}
