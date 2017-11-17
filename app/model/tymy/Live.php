<?php

namespace Tymy;

use Nette;

/**
 * Description of Live
 *
 * @author matej
 */
final class Live extends Tymy{
    
    const TAPI_NAME = "live";
    const TSID_REQUIRED = TRUE;
       
    public function select() {
        $url = self::TAPI_NAME;        
        $this->fullUrl .= $url;
        return $this;
    }
    
    protected function postProcess(){
        if (($data = $this->getData()) == null)
            return;
        
        foreach ($data as &$live) {
            $live->webName = \Nette\Utils\Strings::webalize($live->id . "-" . $live->callName);
        }
        
    }
    
}
