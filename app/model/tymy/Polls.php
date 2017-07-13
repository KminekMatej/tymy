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
    
    public function select() {
        $this->fullUrl .= self::TAPI_NAME;
        return $this;
    }
    
    protected function postProcess(){
        $data = $this->getData();
        
        $this->getResult()->menuWarningCount = 0;
        
        foreach ($data as $poll) {
            $poll->webName = \Nette\Utils\Strings::webalize($poll->caption);
            if($poll->status == "OPENED" && $poll->canVote && !$poll->voted)
                $this->getResult()->menuWarningCount++;
            $this->timezone($poll->createdAt);
            $this->timezone($poll->updatedAt);
        }
    }
}
