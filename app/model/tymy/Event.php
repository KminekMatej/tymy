<?php

namespace Tymy;

use Nette;

/**
 * Description of Tymy
 *
 * @author matej
 */
final class Event extends Tymy{
    
    public function select() {
        if (!isset($this->recId))
            throw new APIException('Event ID not set!');
        
        $this->fullUrl .= "event/" .$this->recId;
        
        return $this;
    }
    
    protected function tzFields($jsonObj) {
        $this->timezone($jsonObj->closeTime);
        $this->timezone($jsonObj->startTime);
        $this->timezone($jsonObj->endTime);
        if (property_exists($jsonObj->attendance, "preDatMod")) {
            $this->timezone($jsonObj->attendance->preDatMod);
        }
    }

}
