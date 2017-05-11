<?php

namespace Tymy;

use Nette;

/**
 * Description of Tymy
 *
 * @author matej
 */
final class Discussions extends Tymy{
    
    private $withNew = FALSE;
    
    public function setWithNew($withNew){
        $this->withNew = $withNew;
        return $this;
    }
    
    public function select() {
        $url = "discussions";
        if($this->withNew)
            $url .= "/withNew";
        $this->fullUrl .= $url;
        return $this;
    }
    
    protected function tzFields($jsonObj){
        if ($this->withNew)
            foreach ($jsonObj as $discussion) {
                $this->timezone($discussion->newInfo->lastVisit);
            }

        return null;
    }
}