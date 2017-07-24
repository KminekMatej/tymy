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
    private $login;
    private $password;
    private $email;
    private $firstName;
    private $lastName;
    private $adminNote;
    
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

        $this->fullUrl .= "users/" .$this->recId . "/edit/";

        $this->urlEnd();
        
        $this->method = "PUT";
        
        foreach ($fields as $key => $value) {
            $this->addPost($key,$value);
        }
        
        $this->result = $this->execute();
        return $this;
    }
    
    public function register(){
        if (!$this->login)
            throw new \Tymy\Exception\APIException('Login not set!');
        if (!$this->password)
            throw new \Tymy\Exception\APIException('Password not set!');
        if (!$this->email)
            throw new \Tymy\Exception\APIException('Email not set!');
        
        $this->urlStart();

        $this->fullUrl .= "users/register/";
        
        $this->method = "POST";
        
        $this->addPost("login",$this->login);
        $this->addPost("password",$this->password);
        $this->addPost("email",$this->email);
        $this->addPost("callName",$this->composeCallName());
        
        if($this->firstName)
            $this->addPost("firstName",$this->firstName);
        if($this->lastName)
            $this->addPost("lastName",$this->lastName);
                
        $this->result = $this->execute();
        
        if($this->result->status == "OK"){
            //$this->sendRegistrationMail();
        }

        return $this;
    }
    
    protected function postProcess(){
        $data = $this->getData();
        $data->webName = \Nette\Utils\Strings::webalize($data->fullName . "-" . $data->fullName);
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
    
    public function getFirstName() {
        return $this->firstName;
    }

    public function getLastName() {
        return $this->lastName;
    }

    public function getAdminNote() {
        return $this->adminNote;
    }

    public function setFirstName($firstName) {
        $this->firstName = $firstName;
        return $this;
    }

    public function setLastName($lastName) {
        $this->lastName = $lastName;
        return $this;
    }

    public function setAdminNote($adminNote) {
        $this->adminNote = $adminNote;
        return $this;
    }

    
    public function reset() {
        $this->login = NULL;
        $this->password = NULL;
        $this->email = NULL;
        $this->firstName = NULL;
        $this->lastName = NULL;
        $this->adminNote = NULL;
        return parent::reset();
    }
    
    private function composeCallName(){
        $callName = $this->firstName . " " . $this->lastName;
        if(trim($callName) == "")
            $callName = $this->login;
        return $callName;
    }
    
    /**
     * @todo Dodelat nette mailung
     */
    private function sendRegistrationMail() {
        $latte = new Latte\Engine;
        $mail = new Message;
        $mail->setFrom('Franta <franta@example.com>')
                ->addTo('petr@example.com')
                ->setHtmlBody($latte->renderToString('email.latte', $params));
    }

}
