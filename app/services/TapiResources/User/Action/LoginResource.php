<?php

namespace Tapi;
use Tymy\Exception\APIException;

/**
 * Project: tymy_v2
 * Description of LoginResource
 *
 * @author kminekmatej created on 29.12.2017, 19:56:00
 */
class LoginResource extends UserResource  {
    
    private $login;
    private $hash;
    
    public function init() {
        $this->setCacheable(FALSE);
        $this->setTsidRequired(FALSE);
    }

    protected function preProcess() {
        if($this->login == null)
            throw new APIException('Login not set!');
        
        $this->setUrl("login/" . $this->login . "/" . $this->hash);

        return $this;
    }
    
    protected function postProcess() {
        
    }
    
    public function setLogin($login) {
        $this->login = $login;
        return $this;
    }

    public function setPassword($password) {
        $h = "";
        $n = rand(1, 19); // password given is already hashed by md5 - therefore max should be 19 to have at most 20 md5 hashings. Min is 1 to have at least one hash
        for ($index = 0; $index < $n; $index++) {
            $h = md5($password);
        }
        $this->hash = $h;
        return $this;
    }


}
