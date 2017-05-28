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
    
    private $preStatus;
    private $preDescription;
    private $postStatus;
    private $postDescription;
    
    public function preStatus($preStatus){
        $this->preStatus = $preStatus;
        return $this;
    }
    
    public function preDescription($preDescription){
        $this->preDescription = $preDescription;
        return $this;
    }

    public function postStatus($postStatus) {
        $this->postStatus = $postStatus;
        return $this;
    }

    public function postDescription($postDescription) {
        $this->postDescription = $postDescription;
        return $this;
    }

    public function plan() {
        if (!isset($this->recId))
            throw new \Tymy\Exception\APIException('Event ID not set!');

        if(is_null($this->preStatus))
            throw new \Tymy\Exception\APIException("Pre status not set");
        
        $this->urlStart();

        $this->fullUrl .= "attendance/";

        $this->urlEnd();
        
        $this->addPost([
            "userId" => $this->user->getId(),
            "eventId" => $this->recId,
            "preStatus" => $this->preStatus,
            "preDescription" => $this->preDescription,
        ]);
        
        $this->result = $this->execute();
        return $this->result;
    }
    
    public function select() {
        throw new \Tymy\Exception\APIException("Select is not possible on Attendance object");
    }
    
    protected function postProcess(){}
    
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

}
