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
        return $this;
    }
    
    public function setPassword($password) {
        $h = "";
        $n = rand(1, 20);
        for ($index = 0; $index < $n; $index++) {
            $h = md5($password);
        }
        $this->hash = $h;
        return $this;
    }
    
    public function select() {
        $this->fullUrl .= "login/".$this->username."/".$this->hash;
        return $this;
    }
    
    public function fetch() {
        $this->urlStart();

        $this->select();

        $this->urlEnd();
        
        $this->result = $this->execute();

        $data = $this->getData();

        $this->tzFields($data);
        
        return $data;
    }

    protected function tzFields($jsonObj){
        return null;
    }
}
