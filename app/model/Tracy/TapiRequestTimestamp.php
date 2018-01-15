<?php

namespace Tapi;

/**
 * Description of TapiRequestTimestamp
 *
 * @author matej
 */
class TapiRequestTimestamp {
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
        return "<table class=\"table\">"
                . "<tr>"
                    . "<th>$this->actionName: </th>"
                    . "<td>". round($this->time * 1000, 1)." ms</td>"
                . "</tr>"
                . "<tr>"
                    . "<td colspan=\"2\" style=\"test-decoration: italic\"><a href=\"$this->actionDesc\" target=_blank>$this->actionDesc</a></td>"
                . "</tr>"
            . "</table>";
    }
}
