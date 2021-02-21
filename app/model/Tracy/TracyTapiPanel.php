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
        $ret = "<h1>".$this->team."</h1>"
                . "<div class=\"tracy-inner\"><table class=\"table\">";
        foreach ($this->requests as $req) {
            $ret .= $req->write();
        }
        return $ret. "</table></div>";
    }
    
    public function team($teamName){
        $this->team = $teamName;
        return $this;
    }
    
    public function logAPI($requestURI, $requestMethod, $time, $responseCode, ?array $requestHeaders = null, $requestData = null, $responseData = null) {
        $reqT = new TapiRequestTimestamp($requestURI, $requestHeaders, $requestMethod, $responseCode, $time, $requestData, $responseData);
        $this->requests[] = $reqT;
    }

}