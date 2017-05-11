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
            
        if(count($filter) && !$this->withMyAttendance){
            $this->setUriParam("filter", join("~", $filter));
        }
        
        $this->fullUrl .= $url;
        return $this;
    }
    
    protected function tzFields($jsonObj){
        foreach ($jsonObj as $event) {
            $this->timezone($event->closeTime);
            $this->timezone($event->startTime);
            $this->timezone($event->endTime);
            if($this->withMyAttendance)
                $this->timezone($event->myAttendance->preDatMod);
        }
    }
}
