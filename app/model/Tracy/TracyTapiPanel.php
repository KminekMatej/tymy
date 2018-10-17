<?php

namespace Tapi;

use Nette\SmartObject;
use Tracy;
/**
 * Description of TracyTapiPanel
 *
 * @author matej
 */
class TracyTapiPanel implements Tracy\IBarPanel{
    
    use SmartObject;
    
    private $requests = [];
    private $team = "API tymy.cz";
    
    public function getTab(){
        return "<span title=\"Týmy API communications\">API</span>";
    }

    public function getPanel(){
        $ret = "<h1>".$this->team."</h1>";
        foreach ($this->requests as $req) {
            $ret .= "<div class=\"tracy-inner\">".$req->write()."</div>";
        }
        return $ret;
    }
    
    public function team($teamName){
        $this->team = $teamName;
        return $this;
    }
    
    public function logAPI($name, $desc, $time) {
        $reqT = new TapiRequestTimestamp();
        $reqT->name($name)
                ->desc($desc)
                ->time($time);
        $this->requests[] = $reqT;
    }

}