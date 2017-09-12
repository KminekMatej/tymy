<?php

namespace Tymy;

use Nette;

/**
 * Description of Tymy
 *
 * @author matej
 */
final class Poll extends Tymy{
    
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
        
        foreach ($votes as $vote) {
            $vote["userId"] = $this->user->getId();
            $this->addPost($vote);
        }
        $this->result = $this->execute();
        return $this->result;
        
    }
    
    protected function postProcess(){
        if ($data = $this->getData() == null)
            return;
        $data->webName = \Nette\Utils\Strings::webalize($data->id . "-" . $data->caption);
        $this->timeLoad($data->createdAt);
        $this->timeLoad($data->updatedAt);
        $data->radio = property_exists($data, "minItems") && property_exists($data, "maxItems") && $data->minItems == 1 && $data->maxItems == 1;
        if ($data->radio) {
            foreach ($data->options as $opt) {
                if ($opt->type != "BOOLEAN") {
                    $data->radio = FALSE;
                    break;
                }
            }
        }

        if (property_exists($data, "votes")){
            $orderedVotes = [];
            foreach ($data->votes as $vote) {
                if (!$data->anonymousResults && $vote->userId == $this->user->getId()) {
                    $data->myVotes[$vote->optionId] = $vote;
                }
                $orderedVotes[$vote->userId][$vote->optionId] = $vote;
                $this->timeLoad($vote->updatedAt);
            }
            $data->orderedVotes = $orderedVotes;
        }
            
    }
    
}
