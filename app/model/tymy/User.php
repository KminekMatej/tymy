<?php

namespace Tymy;

use Nette;
use Nette\Utils\Strings;
use Nette\Mail\Message;

/**
 * Description of Tymy
 *
 * @author matej
 */
final class User extends UserInterface{
    
    const TSID_REQUIRED = TRUE;
    const TAPI_NAME = "user";
    
    public function select() {
        if (!isset($this->recId))
            throw new \Tymy\Exception\APIException('User ID not set!');
        $this->fullUrl .= self::TAPI_NAME . "/" .$this->recId;
        return $this;
    }
    
    public function edit($fields){
        if (!isset($this->recId))
            throw new \Tymy\Exception\APIException('User ID not set!');
        if (!$fields)
            throw new \Tymy\Exception\APIException('Fields to edit not set!');
        
        $this->urlStart();

        $this->fullUrl .= "users/" .$this->recId;

        $this->urlEnd();
        
        $this->method = "PUT";
        
        foreach ($fields as $key => $value) {
            $this->addPost($key,$value);
        }
        
        $this->result = $this->execute();
        return $this;
    }
    
    protected function postProcess(){
        if (($data = $this->getData()) == null)
            return;
        $data->webName = (string)$data->id;
        if(property_exists($data, "fullName")) $data->webName .= "-" . Strings::webalize($data->fullName);
        if(!property_exists($data, "gender")) $data->gender = "UNKNOWN"; //set default value
        if(!property_exists($data, "language")) $data->language = "CZ"; //set default value
        if(!property_exists($data, "canEditCallName")) $data->canEditCallName = true; //set default value
        if(property_exists($data, "lastLogin")){
                $this->timeLoad($data->lastLogin);
            }
        $this->userWarnings($data);
        $this->userPermissions($data);
    }

}
