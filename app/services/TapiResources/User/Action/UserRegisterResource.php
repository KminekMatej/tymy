<?php

namespace Tapi;
use App\Model\Supplier;
use Tymy\Exception\APIException;

/**
 * Project: tymy_v2
 * Description of UserRegisterResource
 *
 * @author kminekmatej created on 29.12.2017, 20:07:54
 */
class UserRegisterResource extends UserResource {
    
    private $login;
    private $password;
    private $email;
    private $callName;
    private $firstName;
    private $lastName;
    private $note;
         
    public function __construct(Supplier $supplier) {
        parent::__construct($supplier, NULL, NULL, NULL);
    }
    
    public function init() {
        $this->setCacheable(FALSE);
        $this->setMethod(RequestMethod::POST);
        $this->setTsidRequired(FALSE);
    }

    protected function preProcess() {
        if (!$this->login)
            throw new APIException('Login not set!');
        if (!$this->password)
            throw new APIException('Password not set!');
        if (!$this->email)
            throw new APIException('Email not set!');
        
        $this->setUrl("users/register");
        
        $data = [
            "login" => $this->login,
            "password" => $this->password,
            "email" => $this->email,
            "note" => $this->note,
            "callName" => $this->composeCallName()
        ];
        
        
        
        if($this->firstName){
            if (count($this->firstName) > 20)
                throw new APIException('First name too long!');
            $data["firstName"] = $this->firstName;
        }
            
        if($this->lastName){
            if (count($this->lastName) > 20)
                throw new APIException('Last name too long!');
            $data["lastName"] = $this->lastName;
        }
        
        $this->setRequestData((object)$data);

        return $this;
    }
    
    protected function postProcess() {
        //returns user object, but its not used anywhere, so no post processing needed
    }
    
    private function composeCallName(){
        $callName = $this->firstName . " " . $this->lastName;
        if(trim($callName) == "")
            $callName = $this->login;
        return substr($callName, 0, 30);
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

    public function getCallName() {
        return $this->callName;
    }

    public function getFirstName() {
        return $this->firstName;
    }

    public function getLastName() {
        return $this->lastName;
    }

    public function getNote() {
        return $this->note;
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

    public function setCallName($callName) {
        $this->callName = $callName;
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

    public function setNote($note) {
        $this->note = $note;
        return $this;
    }


    
}
