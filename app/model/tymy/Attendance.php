<?php

namespace Tymy;

use Nette;
use Nette\Utils\Json;

/**
 * Description of Attendance
 *
 * @author matej
 */
final class Attendance extends Tymy{
    
    const TAPI_NAME = "attendance";
    const TSID_REQUIRED = TRUE;
    
    private $preStatus;
    private $preDescription;
    private $postStatus;
    private $postDescription;
    
    public function setPreStatus($preStatus){
        $this->preStatus = $preStatus;
        return $this;
    }
    
    public function setPreDescription($preDescription){
        $this->preDescription = $preDescription;
        return $this;
    }

    public function setPostStatus($postStatus) {
        $this->postStatus = $postStatus;
        return $this;
    }

    public function setPostDescription($postDescription) {
        $this->postDescription = $postDescription;
        return $this;
    }

    public function plan() {
        if (!isset($this->recId))
            throw new \Tymy\Exception\APIException('Event ID not set!');

        if(!isset($this->preStatus))
            throw new \Tymy\Exception\APIException("Pre status not set");
        
        $this->urlStart();

        $this->fullUrl .= self::TAPI_NAME;

        $this->urlEnd();
        
        $this->method = "POST";
        
        $data = new \stdClass();
        $data->userId = $this->user->getId();
        $data->eventId = $this->recId;
        $data->preStatus = $this->preStatus;
        $data->preDescription = $this->preDescription;
        
        $this->setPostData([$data]);
        
        $this->result = $this->execute();
        return $this->result;
    }
    
    public function confirm($postStatuses) {
        if (!isset($this->recId))
            throw new \Tymy\Exception\APIException('Event ID not set!');

        $this->urlStart();

        $this->fullUrl .= self::TAPI_NAME;

        $this->urlEnd();
        
        $this->method = "POST";
        
        foreach ($postStatuses as $status) {
            $status["eventId"] = $this->recId;
        }
        $this->setPostData([$status]);
        
        $this->result = $this->execute();
        return $this->result;
    }
    
    public function select() {
        throw new \Tymy\Exception\APIException("Select is not possible on Attendance object");
    }
    
    protected function postProcess() {
        if (($data = $this->getData()) == null)
            return;
    }

    public function getPreStatus(){
        return $this->preStatus;
    }

    public function getPreDescription(){
        return $this->preDescription;
    }
    
    public function getPostStatus(){
        return $this->postStatus;
    }

    public function getPostDescription(){
        return $this->postDescription;
    }
    
    public function reset() {
        $this->preDescription = NULL;
        $this->preStatus = NULL;
        $this->postDescription = NULL;
        $this->postStatus = NULL;
        return parent::reset();
    }


}
