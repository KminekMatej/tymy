<?php

namespace Tymy;

use Nette;

/**
 * Description of Tymy
 *
 * @author matej
 */
final class Poll extends Tymy{
    
    public function select() {
        if (!isset($this->recId))
            throw new APIException('Poll ID not set!');
        
        $this->fullUrl .= "polls/" .$this->recId;
        return $this;
    }
    
    protected function tzFields($jsonObj){
        $this->timezone($jsonObj->createdAt);
        $this->timezone($jsonObj->updatedAt);
    }
}
