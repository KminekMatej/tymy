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
    
    public function __construct(Nette\Application\UI\Presenter $presenter = NULL) {
        parent::__construct($presenter);
    }
    
    public function select() {
        $this->fullUrl .= "eventTypes/";
    }
    
    protected function postProcess(){
        return TRUE;
    }

}
