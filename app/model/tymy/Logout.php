<?php

namespace Tymy;

use Nette;
use Nette\Utils\Json;

/**
 * Description of Tymy
 *
 * @author matej
 */
final class Logout extends Tymy{
    
    const TAPI_NAME = "logout";
    const TSID_REQUIRED = TRUE;
    
    public function select() {
        return $this;
    }
    
    public function logout(){
        $this->urlStart();

        $this->fullUrl .= self::TAPI_NAME;

        $this->urlEnd();
        
        return $this->execute();
    }
    
    protected function postProcess(){}
    
}
