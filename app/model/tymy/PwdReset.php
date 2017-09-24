<?php

namespace Tymy;

use Nette;
use Nette\Utils\Json;

/**
 * Description of Tymy
 *
 * @author matej
 */
final class PwdReset extends Tymy{
    
    const TAPI_NAME = "pwdreset";
    const TSID_REQUIRED = FALSE;
    
    private $code;
    
    public function select() {
        if (!isset($this->code))
            throw new \Tymy\Exception\APIException('Code not set!');
        
        $this->fullUrl .= self::TAPI_NAME . "/" . $this->code;
        
        return $this;
    }
        
    protected function postProcess() {
        if (($data = $this->getData()) == null)
            return;
    }
    
    public function getCode() {
        return $this->code;
    }

    public function setCode($code) {
        $this->code = $code;
        return $this;
    }

                
    public function reset() {
        $this->code = NULL;
        return parent::reset();
    }

}
