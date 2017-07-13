<?php

namespace Tymy;

use Nette;
use Nette\Utils\Strings;

/**
 * Description of Tymy
 *
 * @author matej
 */
final class User extends UserInterface{
    
    const TSID_REQUIRED = TRUE;
    const TAPI_NAME = "user";
    private $login;
    private $password;
    private $email;
    
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

        $this->fullUrl .= "users/" .$this->recId . "/edit/";

        $this->urlEnd();
        
        foreach ($fields as $key => $value) {
            $this->addPost($key,$value);
        }
        
        $this->result = $this->execute();
        return $this;
    }
    
    public function create(){
        if (!$this->login)
            throw new \Tymy\Exception\APIException('Login not set!');
        if (!$this->password)
            throw new \Tymy\Exception\APIException('Password not set!');
        if (!$this->email)
            throw new \Tymy\Exception\APIException('Email not set!');
        
        $this->urlStart();

        $this->fullUrl .= "users/register/";
        
        foreach ($fields as $key => $value) {
            $this->addPost($key,$value);
        }
        
        $this->result = $this->execute();
        return $this;
    }
    
    protected function postProcess(){
        $data = $this->getData();
        $data->webName = \Nette\Utils\Strings::webalize($data->fullName);
        if(!property_exists($data, "gender")) $data->gender = "UNKNOWN"; //set default value
        if(property_exists($data, "lastLogin")){
                $this->timezone($data->lastLogin);
            }
        $this->userWarnings($data);
        $this->userPermissions($data);
    }
    
    public function getLogin() {
        return $this->login;
    }

    public function getPassword() {
        return $this->password;
    }

    public function getEmail() {
        return $this->email;
    }

    public function setLogin($login) {
        $this->login = $login;
        return $this;
    }

    public function setPassword($password) {
        $this->password = $password;
        return $this;
    }

    public function setEmail($email) {
        $this->email = $email;
        return $this;
    }


}
