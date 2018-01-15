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
        
        $this->result = $this->execute();
        
        $this->postProcess();
        
        return $this->result;
    }
    
    protected function postProcess(){
        if (!is_null($this->session)) {
            $this->session->getSection(self::SESSION_SECTION)->remove(); // destroy TAPI session section
        }
    }
    
}
