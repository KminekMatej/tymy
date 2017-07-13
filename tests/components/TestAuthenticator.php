<?php

namespace App\Model;

use Nette;

/**
 * Users management.
 */
class TestAuthenticator implements Nette\Security\IAuthenticator {

    private $id;
    private $status;
    private $arr;
    
    /**
     * Performs an authentication.
     * @return Nette\Security\Identity
     * @throws Nette\Security\AuthenticationException
     */
    public function authenticate(array $credentials) {
        list($username, $password) = $credentials;
        $dataMock = new \stdClass();
        $dataMock->login = $username;
        $this->setId(38);
        $this->setStatus(["TESTROLE", "TESTROLE2"]);
        $this->setArr([
            "tym" => "testteam", 
            "sessionKey" => "dsfbglsdfbg13546",
            "hash" => "123hash",
            "data" => $dataMock
            ]);
        return new Nette\Security\Identity($this->id, $this->status, $this->arr );
    }
    
    public function setId($id){
        $this->id = $id;
    }
    
    public function setStatus($status){
        $this->status = $status;
    }
    
    public function setArr($arr){
        $this->arr = $arr;
    }
}