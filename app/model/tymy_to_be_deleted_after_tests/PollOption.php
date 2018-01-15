<?php

namespace Tymy;

use Nette;

/**
 * Description of Tymy
 *
 * @author matej
 */
final class PollOption extends Tymy {
    
    const TAPI_NAME = "polls";
    const TSID_REQUIRED = TRUE;
    
    private $optionId;
    
    public function getOptionId() {
        return $this->optionId;
    }

    public function setOptionId($optionId) {
        $this->optionId = $optionId;
        return $this;
    }

        
    public function select() {
        if (!isset($this->recId))
            throw new \Tapi\Exception\APIException('Poll ID not set!');
        
        $this->fullUrl .= self::TAPI_NAME . "/" .$this->recId . "/options";
        
        return $this;
    }
        
    public function edit($fields){
        if (!isset($this->recId))
            throw new \Tapi\Exception\APIException('Poll ID not set!');
        if (!isset($this->optionId))
            throw new \Tapi\Exception\APIException('Option ID not set!');
        if (!$fields)
            throw new \Tapi\Exception\APIException('Fields to edit not set!');
        if (!$this->user->isAllowed("SYS","ASK.VOTE_UPDATE"))
            throw new \Tapi\Exception\APIException('Permission denied!');
        
        $fields["id"] = $this->getOptionId();
                
        $this->urlStart();

        $this->fullUrl .= self::TAPI_NAME . "/" .$this->recId . "/options";

        $this->urlEnd();
        
        $this->method = "PUT";
        
        $this->setPostData((object)$fields);
        
        $this->result = $this->execute();
        return $this;
    }
    
    public function delete(){
        if (!isset($this->recId))
            throw new \Tapi\Exception\APIException('Poll ID not set!');
        if (!isset($this->optionId))
            throw new \Tapi\Exception\APIException('Option ID not set!');
        if (!$this->user->isAllowed("SYS","ASK.VOTE_DELETE"))
            throw new \Tapi\Exception\APIException('Permission denied!');
        
        $this->urlStart();

        $this->fullUrl .= self::TAPI_NAME . "/" .$this->recId . "/options";

        $this->urlEnd();
        
        $this->method = "DELETE";
        
        $this->setPostData((object)["id" => $this->getOptionId()]);

        $this->result = $this->execute();
        return $this;
    }
    
    public function create($optionsArray){
        if (!isset($this->recId))
            throw new \Tapi\Exception\APIException('Poll ID not set!');
        if (!$optionsArray)
            throw new \Tapi\Exception\APIException('Fields to create not set!');
        if (!$this->user->isAllowed("SYS", "ASK.VOTE_UPDATE"))
            throw new \Tapi\Exception\APIException('Permission denied!');
        foreach ($optionsArray as $option) {
            if(!array_key_exists("caption", $option))
                throw new \Tapi\Exception\APIException('Caption not set!');
            if(!array_key_exists("type", $option))
                throw new \Tapi\Exception\APIException('Type not set!');
        }
        
        $this->urlStart();

        $this->fullUrl .= self::TAPI_NAME . "/" .$this->recId . "/options";
        
        $this->method = "POST";
        
        $this->setPostData($optionsArray);
        
        $this->result = $this->execute();

        return $this;
    }
    
    protected function postProcess(){
        if (($data = $this->getData()) == null)
            return;
        parent::postProccess($data);
    }
    
}
