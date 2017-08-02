<?php

namespace Tymy;

use Nette;

/**
 * Description of Tymy
 *
 * @author matej
 */
final class Register extends Tymy{
    
    const TAPI_NAME = "users/register";
    const TSID_REQUIRED = FALSE;
    
    private $login;
    private $password;
    private $email;
    private $firstName;
    private $lastName;
    private $admin_note;
    
    public function __construct(\App\Model\Supplier $supplier) {
        $this->supplier = $supplier;
        $this->initTapiDebugPanel();
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

    public function setFirstName($firstName) {
        $this->firstName = $firstName;
        return $this;
    }

    public function setLastName($lastName) {
        $this->lastName = $lastName;
        return $this;
    }

    public function setAdmin_note($admin_note) {
        $this->admin_note = $admin_note;
        return $this;
    }

        
    public function register() {
        if (!$this->login)
            throw new \Tymy\Exception\APIException('Login not set!');
        if (!$this->password)
            throw new \Tymy\Exception\APIException('Password not set!');
        if (!$this->email)
            throw new \Tymy\Exception\APIException('Email not set!');
        
        $this->urlStart();

        $this->fullUrl .= self::TAPI_NAME;
        
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
    
    public function fetch() {
        return $this;
    }
    
    public function select() {
        return $this;
    }

    protected function postProcess(){}
}
