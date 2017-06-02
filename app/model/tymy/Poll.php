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
    
    protected function postProcess(){
        $data = $this->getData();
        $data->webName = \Nette\Utils\Strings::webalize($data->caption);
        $this->timezone($data->createdAt);
        $this->timezone($data->updatedAt);
        foreach ($data->votes as $vote) {
            if($vote->userId == $this->user->getId()){
                $data->myVotes[] = $vote;
            }
            $this->timezone($vote->updatedAt);
        }
    }
}
