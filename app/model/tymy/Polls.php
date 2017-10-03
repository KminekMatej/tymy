<?php

namespace Tymy;

use Nette;

/**
 * Description of Tymy
 *
 * @author matej
 */
final class Polls extends Tymy{
    
    const TAPI_NAME = "polls";
    const TSID_REQUIRED = TRUE;
    
    private $menu = FALSE;
    
    public function select() {
        $this->fullUrl .= self::TAPI_NAME;
        
        if($this->menu) $this->fullUrl .= "/menu";
        
        return $this;
    }
    
    protected function postProcess(){
        if (($data = $this->getData()) == null)
            return;
        
        $this->getResult()->menuWarningCount = 0;
        
        foreach ($data as $poll) {
            $poll->webName = \Nette\Utils\Strings::webalize($poll->id . "-" . $poll->caption);
            if($poll->status == "OPENED" && $poll->canVote && !$poll->voted)
                $this->getResult()->menuWarningCount++;
            $this->timeLoad($poll->createdAt);
            $this->timeLoad($poll->updatedAt);
        }
    }
    
    public function getMenu() {
        return $this->menu;
    }

    public function setMenu($menu) {
        $this->menu = $menu;
        return $this;
    }


}
