<?php

namespace Tymy;

/**
 * Description of TymyRequestTimestamp
 *
 * @author matej
 */
class TymyRequestTimestamp {
    private $actionName;
    private $actionDesc;
    private $time;
    
    public function name($actionName){
        $this->actionName = $actionName;
        return $this;
    }

    public function desc($actionDescription){
        $this->actionDesc = $actionDescription;
        return $this;
    }
    
    public function time($time){
        $this->time = $time;
        return $this;
    }
    
    public function write(){
        return "<table class=\"table\"><tr><th>$this->actionName: </th><td>". round($this->time * 1000)." ms</td></tr><tr><td colspan=\"2\" style=\"test-decoration: italic\">$this->actionDesc</td></tr></table>";
    }
}
