<?php

namespace Tymy;

use Nette;
use Nette\Utils\Strings;

/**
 * Description of Tymy
 *
 * @author matej
 */
final class EventTypes extends Tymy{
    
    const TAPI_NAME = "eventTypes";
    const TSID_REQUIRED = TRUE;
    
    public function select() {
        $this->fullUrl .= self::TAPI_NAME;
    }
    
    protected function postProcess(){}

}
