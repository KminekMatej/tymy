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
            throw new \Tapi\Exception\APIException('Login not set!');
        if (!$this->password)
            throw new \Tapi\Exception\APIException('Password not set!');
        if (!$this->email)
            throw new \Tapi\Exception\APIException('Email not set!');
        
        $this->urlStart();

        $this->fullUrl .= self::TAPI_NAME;
        
        $this->method = "POST";
        
        $data = new \stdClass();
        $data->login = $this->login;
        $data->password = $this->password;
        $data->email = $this->email;
        $data->note = $this->admin_note;
        $data->callName = $this->composeCallName();
        
        if($this->firstName){
            if (count($this->firstName) > 20)
                throw new \Tapi\Exception\APIException('First name too long!');
            $data->firstName = $this->firstName;
        }
            
        if($this->lastName){
            if (count($this->lastName) > 20)
                throw new \Tapi\Exception\APIException('Last name too long!');
            $data->lastName = $this->lastName;
        }
        
        $this->setPostData($data);
                
        $this->result = $this->execute();
        
        return $this;
    }
    
    private function composeCallName(){
        $callName = $this->firstName . " " . $this->lastName;
        if(trim($callName) == "")
            $callName = $this->login;
        return substr($callName, 0, 30);
    }
    
    
    /**
     * @todo Dodelat nette mailing
     */
    private function sendRegistrationMail() {
        $latte = new Latte\Engine;
        $mail = new Message;
        $mail->setFrom('Franta <franta@example.com>')
                ->addTo('petr@example.com')
                ->setHtmlBody($latte->renderToString('email.latte', $params));
    }
        
    public function select() {
        return $this;
    }

    protected function postProcess() {
        if (($data = $this->getData()) == null)
            return;
    }

}
