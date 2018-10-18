<?php

namespace Tapi;

/**
 * Description of TapiRequestTimestamp
 *
 * @author matej
 */
class TapiRequestTimestamp {
    
    private $requestURI;
    private $requestMethod;
    private $requestData;
    private $responseCode;
    private $time;
    
    public function __construct($requestURI, $requestMethod, $requestData, $time, $responseCode) {
        $this->requestURI = $requestURI;
        $this->requestMethod = $requestMethod;
        $this->requestData = $requestData;
        $this->responseCode = $responseCode;
        $this->time = $time;
    }
    
    public function write(){
        $html = "<tr>"
                . "<th>";
        if($this->requestMethod != "GET") $html .= "<span class=\"tracy-toggle tracy-collapsed\"><span class=\"tracy-dump-array\">";
        $html .= $this->requestMethod;
        if($this->requestMethod != "GET") $html .= "</span></span>";
        if($this->requestMethod != "GET"){ //add data to collapse
            $html .= "<div class=\"tracy-collapsed\">";
            $html .= "<span style='font-weight: normal; font-style: italic'>".$this->requestData."</span>";
            $html .= "</div>";
        }
        $html .= "</th>";
        $html .= "<td><a href=\"".$this->requestURI."\" target=_blank>".$this->requestURI."</a></td>";
        $html .= "<td>". round($this->time * 1000, 1)." ms</td>";
        $html .= "<th>". $this->responseCode . "</th>";
        $html .= "</tr>";
        return $html;
    }
}
