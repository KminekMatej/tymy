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
    
    public function edit($fields){
        if (!isset($this->recId))
            throw new \Tymy\Exception\APIException('User ID not set!');
        if (!$fields)
            throw new \Tymy\Exception\APIException('Fields to edit not set!');
        if (!$this->user->isAllowed("SYS","ASK.VOTE_UPDATE"))
            throw new \Tymy\Exception\APIException('Permission denied!');
        
        
        $this->urlStart();

        $this->fullUrl .= "polls/" .$this->recId;

        $this->urlEnd();
        
        $this->method = "PUT";
        
        $this->setPostData((object)$fields);
        
        $this->result = $this->execute();
        return $this;
    }
    
    public function delete(){
        if (!isset($this->recId))
            throw new \Tymy\Exception\APIException('User ID not set!');
        if (!$this->user->isAllowed("SYS","EVE_DELETE"))
            throw new \Tymy\Exception\APIException('Permission denied!');
        
        $this->urlStart();

        $this->fullUrl .= self::TAPI_NAME . "/" .$this->recId;

        $this->urlEnd();
        
        $this->method = "DELETE";
                
        $this->result = $this->execute();
        return $this;
    }
    
    public function create($eventsArray, $eventTypesArray){
        foreach ($eventsArray as $event) {
            if(!array_key_exists("startTime", $event))
                throw new \Tymy\Exception\APIException('Start time not set!');
            if(!array_key_exists("type", $event))
                throw new \Tymy\Exception\APIException('Type not set!');
            if(!array_key_exists($event["type"], $eventTypesArray))
                throw new \Tymy\Exception\APIException('Unrecognized type!');
        }
        
        $this->urlStart();

        $this->fullUrl .= "events";
        
        $this->method = "POST";
        
        $this->setPostData($eventsArray);
        
        $this->result = $this->execute();

        return $this;
    }
    
    protected function postProcess(){
        if (($data = $this->getData()) == null)
            return;
        parent::postProccess($data);
    }
    
}
