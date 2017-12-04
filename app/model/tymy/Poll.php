<?php

namespace Tymy;

use Nette;

/**
 * Description of Tymy
 *
 * @author matej
 */
final class Poll extends PollAbstraction {
    
    const TAPI_NAME = "poll";
    const TSID_REQUIRED = TRUE;
    
    public function select() {
        if (!isset($this->recId))
            throw new \Tymy\Exception\APIException('Poll ID not set!');
        
        $this->fullUrl .= "polls/" .$this->recId;
        return $this;
    }
    
    public function vote($votes){
        if (!isset($this->recId))
            throw new \Tymy\Exception\APIException('Poll ID not set!');
        
        $this->urlStart();

        $this->fullUrl .= "polls/" .$this->recId . "/votes";

        $this->urlEnd();
        
        $this->method = "POST";
        
        foreach ($votes as &$vote) {
            $vote["userId"] = $this->user->getId();
        }
        $this->setPostData($votes);
        
        $this->result = $this->execute();
        return $this->result;
        
    }
    
    protected function postProcess(){
        if (($data = $this->getData()) == null)
            return;
        parent::postProccess($data);
    }
    
}
