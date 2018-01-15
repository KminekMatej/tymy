<?php

namespace Tymy;

use Nette;

/**
 * Description of Tymy
 *
 * @author matej
 */
final class Polls extends PollAbstraction{
    
    const TAPI_NAME = "polls";
    const TSID_REQUIRED = TRUE;
    
    private $menu = FALSE;
    
    public function select() {
        $this->fullUrl .= self::TAPI_NAME;
        
        if($this->menu) $this->fullUrl .= "/menu";
        
        return $this;
    }
    
    protected function postProcess(){
        $this->getResult()->menuWarningCount = 0;
        
        if (($data = $this->getData()) == null)
            return;
        
        foreach ($data as $poll) {
            parent::postProccess($poll);
        }
    }
    
    public function getMenu() {
        return $this->menu;
    }

    public function setMenu($menu) {
        $this->menu = $menu;
        return $this;
    }


}
