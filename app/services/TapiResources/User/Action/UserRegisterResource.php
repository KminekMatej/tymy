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
    
    protected function init() {
        $this->setCacheable(FALSE);
        $this->setMethod(RequestMethod::POST);
        $this->setTsidRequired(FALSE);
        $this->setCallName(NULL);
        $this->setEmail(NULL);
        $this->setFirstName(NULL);
        $this->setLastName(NULL);
        $this->setLogin(NULL);
        $this->setNote(NULL);
        $this->setPassword(NULL);
    }

    protected function preProcess() {
        if (!$this->getLogin())
            throw new APIException('Login not set!');
        if (!$this->getPassword())
            throw new APIException('Password not set!');
        if (!$this->getEmail())
            throw new APIException('Email not set!');
        
        $this->setUrl("users/register");
        
        $data = [
            "login" => $this->getLogin(),
            "password" => $this->getPassword(),
            "email" => $this->getEmail(),
            "note" => $this->getNote(),
            "callName" => $this->composeCallName()
        ];
        
        
        
        if($this->getFirstName()){
            if (count($this->getFirstName()) > 20)
                throw new APIException('First name too long!');
            $data["firstName"] = $this->getFirstName();
        }
            
        if($this->getLastName()){
            if (count($this->getLastName()) > 20)
                throw new APIException('Last name too long!');
            $data["lastName"] = $this->getLastName();
        }
        
        $this->setRequestData((object)$data);

        return $this;
    }
    
    protected function postProcess() {
        //returns user object, but its not used anywhere, so no post processing needed
    }
    
    private function composeCallName(){
        $callName = $this->getFirstName() . " " . $this->getLastName();
        if(trim($callName) == "")
            $callName = $this->getLogin();
        return substr($callName, 0, 30);
    }
    
    public function getLogin() {
        return $this->options->login;
    }

    public function getPassword() {
        return $this->options->password;
    }

    public function getEmail() {
        return $this->options->email;
    }

    public function getCallName() {
        return $this->options->callName;
    }

    public function getFirstName() {
        return $this->options->firstName;
    }

    public function getLastName() {
        return $this->options->lastName;
    }

    public function getNote() {
        return $this->options->note;
    }

    public function setLogin($login) {
        $this->options->login = $login;
        return $this;
    }

    public function setPassword($password) {
        $this->options->password = $password;
        return $this;
    }

    public function setEmail($email) {
        $this->options->email = $email;
        return $this;
    }

    public function setCallName($callName) {
        $this->options->callName = $callName;
        return $this;
    }

    public function setFirstName($firstName) {
        $this->options->firstName = $firstName;
        return $this;
    }

    public function setLastName($lastName) {
        $this->options->lastName = $lastName;
        return $this;
    }

    public function setNote($note) {
        $this->options->note = $note;
        return $this;
    }


    
}
