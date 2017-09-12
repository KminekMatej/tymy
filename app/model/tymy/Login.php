<?php

namespace Tymy;

use Nette;

/**
 * Description of Tymy
 *
 * @author matej
 */
final class Login extends Tymy{
    
    const TAPI_NAME = "login";
    const TSID_REQUIRED = FALSE;
    private $username;
    private $hash;
    
    public function __construct(\App\Model\Supplier $supplier) {
        $this->supplier = $supplier;
        $this->initTapiDebugPanel();
    }
    
    public function setUsername($username) {
        $this->username = $username;
        return $this;
    }
    
    public function setPassword($password) {
        $h = "";
        $n = rand(1, 19); // password given is already hashed by md5 - therefore max should be 19 to have at most 20 md5 hashings
        for ($index = 0; $index < $n; $index++) {
            $h = md5($password);
        }
        $this->hash = $h;
        return $this;
    }
    
    public function select() {
        $this->fullUrl .= self::TAPI_NAME . "/" . $this->username . "/" . $this->hash;
        return $this;
    }
    
    protected function postProcess(){
        if ($data = $this->getData() == null)
            return;
        $this->timeLoad($data->lastLogin);
        if(!property_exists($data, "roles"))
            $data->roles = [];
    }
}
