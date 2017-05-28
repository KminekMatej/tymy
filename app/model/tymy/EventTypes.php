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
    
    public function select() {
        $this->fullUrl .= "eventTypes/";
    }
    
    protected function postProcess(){}

}
