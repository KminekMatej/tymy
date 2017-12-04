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
final class User extends UserAbstraction{
    
    const TSID_REQUIRED = TRUE;
    const TAPI_NAME = "users";
    
    public function select() {
        if (!isset($this->recId))
            throw new \Tymy\Exception\APIException('User ID not set!');
        $this->fullUrl .= "user/" .$this->recId;
        return $this;
    }
    
    public function edit($fields){
        if (!isset($this->recId))
            throw new \Tymy\Exception\APIException('User ID not set!');
        if (!$fields)
            throw new \Tymy\Exception\APIException('Fields to edit not set!');
        
        $this->urlStart();

        $this->fullUrl .= self::TAPI_NAME . "/" .$this->recId;

        $this->urlEnd();
        
        $this->method = "PUT";
        
        $this->setPostData($fields);
        
        $this->result = $this->execute();
        return $this;
    }

    public function setAvatar($avatarBase64){
        if (!isset($this->recId))
            throw new \Tymy\Exception\APIException('User ID not set!');
        if (!$avatarBase64 || !preg_match('/^data:(\w+)\/(\w+);base64,(.*)/', $avatarBase64))
            throw new \Tymy\Exception\APIException('Avatar not set!');
        
        $this->setJsonEncoding(FALSE);
        
        $this->urlStart();

        $this->fullUrl .= self::TAPI_NAME . "/" .$this->recId . "/avatar";

        $this->urlEnd();
        
        $this->method = "POST";
        
        $this->setPostData($avatarBase64);
        
        $this->result = $this->execute();
        return $this;
    }

    
    protected function postProcess(){
        if (($data = $this->getData()) == null)
            return;
        $data->webName = (string)$data->id;
        if(property_exists($data, "fullName")) $data->webName .= "-" . Strings::webalize($data->displayName);
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
