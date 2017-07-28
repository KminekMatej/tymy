<?php

namespace Tymy;

use Nette;

/**
 * Description of Tymy
 *
 * @author matej
 */
final class Event extends Tymy{
    
    const TAPI_NAME = "event";
    const TSID_REQUIRED = TRUE;
    
    public function select() {
        if (!isset($this->recId))
            throw new Exception\APIException('Event ID not set!');
        
        $this->fullUrl .= self::TAPI_NAME . "/" .$this->recId;
        
        return $this;
    }
    
    public function edit($fields){
        if (!isset($this->recId))
            throw new \Tymy\Exception\APIException('User ID not set!');
        if (!$fields)
            throw new \Tymy\Exception\APIException('Fields to edit not set!');
        
        $this->urlStart();

        $this->fullUrl .= self::TAPI_NAME . "/" .$this->recId . "/edit";

        $this->urlEnd();
        
        $this->method = "PUT";
        
        foreach ($fields as $key => $value) {
            $this->addPost($key,$value);
        }
        
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

        $this->fullUrl .= "events/create";
        
        $this->method = "POST";
        
        $this->addPost($eventsArray);
        
        $this->result = $this->execute();

        return $this;
    }
    
    protected function postProcess() {
        $data = $this->getData();
        
        $data->webName = \Nette\Utils\Strings::webalize($data->id . "-" . $data->caption);

        $this->timezone($data->closeTime);
        $this->timezone($data->startTime);
        $this->timezone($data->endTime);
        $myAttendance = new \stdClass();
        $myAttendance->preStatus = "UNKNOWN";
        $myAttendance->postStatus = "UNKNOWN";
        $myAttendance->preDescription = "";
        $myAttendance->postDescription = "";
        if (property_exists($data, "attendance"))
            foreach ($data->attendance as $att) {
                if(!property_exists($att, "preStatus")) $att->preStatus = "UNKNOWN"; //set default value
                if(!property_exists($att, "preDescription")) $att->preDescription = ""; //set default value
                if(!property_exists($att, "postStatus")) $att->postStatus = "UNKNOWN"; //set default value
                if(!property_exists($att, "postDescription")) $att->postDescription = ""; //set default value
                if (property_exists($att, "preDatMod"))
                    $this->timezone($att->preDatMod);
                if (property_exists($att, "postDatMod"))
                    $this->timezone($att->postDatMod);
                if($att->userId == $this->user->getId()){
                    $myAttendance = $att;
                }
            }
        $data->myAttendance = $myAttendance;
    }
    
}
