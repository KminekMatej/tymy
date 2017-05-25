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
