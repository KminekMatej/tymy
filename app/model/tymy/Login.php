<?php

namespace Tymy;

use Nette;

/**
 * Description of Tymy
 *
 * @author matej
 */
final class Login extends Tymy{
    
    private $username;
    private $hash;
    
    public function setUsername($username) {
        $this->username = $username;
    }
    
    public function setPassword($password) {
        $hash = $password;
        $n = rand(1, 20);
        for ($index = 0; $index < $n; $index++) {
            $hash = md5($password);
        }
        $this->hash = $password;
    }
    
    public function select() {
        $this->fullUrl .= "login/".$this->username."/".$this->hash;
        return $this;
    }
    
    protected function tzFields($jsonObj){
        return null;
    }
}
