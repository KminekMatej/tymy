<?php

namespace Tymy;

use Nette;
use Tracy;
/**
 * Description of TracyPanelTymy
 *
 * @author matej
 */
class TracyPanelTymy extends Nette\Object implements Tracy\IBarPanel{
    
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
        $reqT = new TymyRequestTimestamp();
        $reqT->name($name)
                ->desc($desc)
                ->time($time);
        $this->requests[] = $reqT;
    }

}