<?php

namespace Tymy;

use Nette;

/**
 * Description of Tymy
 *
 * @author matej
 */
final class Polls extends Tymy{
    
    public function select() {
        $url = "polls";
        
        $this->fullUrl .= $url;
        return $this;
    }
    
    protected function tzFields($jsonObj){
        foreach ($jsonObj as $poll) {
            $this->timezone($poll->createdAt);
            $this->timezone($poll->updatedAt);
        }
    }
}
