<?php

namespace Tapi;

/**
 * Description of TapiRequestTimestamp
 *
 * @author matej
 */
class TapiRequestTimestamp {
    
    private $requestURI;
    private $requestHeaders;
    private $requestMethod;
    private $requestData;
    private $responseData;
    private $responseCode;
    private $time;
    
    public function __construct(string $requestURI, ?array $requestHeaders, string $requestMethod, int $responseCode, float $time, ?string $requestData = null, ?string $responseData = null)
    {
        $this->requestURI = $requestURI;
        $this->requestHeaders = $requestHeaders;
        $this->requestMethod = $requestMethod;
        $this->requestData = $requestData;
        $this->responseData = $responseData;
        $this->responseCode = $responseCode;
        $this->time = $time;
    }
    
    public function write()
    {        
        $headerAsTitle = !empty($this->requestHeaders) ? ("title='". json_encode($this->requestHeaders) . "'") : "";
        
        $html = "<tr>"
                . "<th $headerAsTitle>";
        
        if ($this->requestMethod != "GET") {
            $html .= "<span class=\"tracy-toggle tracy-collapsed\"><span class=\"tracy-dump-array\">";
        }
        $html .= $this->requestMethod;
        if ($this->requestMethod != "GET") {
            $html .= "</span></span>";
        }
        if ($this->requestMethod != "GET") { //add data to collapse
            $html .= "<div class=\"tracy-collapsed\">";
            $html .= "<span style='font-weight: normal; font-style: italic'>". (is_array($this->requestData) ? print_r($this->requestData, true) : htmlspecialchars($this->requestData))."</span>";
            $html .= "</div>";
        }
        $html .= "</th>";
        $html .= "<td><a href=\"" . urldecode($this->requestURI) . "\" target=_blank>" . urldecode($this->requestURI) . "</a></td>";
        $html .= "<td>" . round($this->time * 1000, 1) . " ms</td>";
        $html .= "<th>" . $this->responseCode;
        $html .= "<span class=\"tracy-toggle tracy-collapsed\"><span class=\"tracy-dump-array\"></span></span>";
        $html .= "<div class=\"tracy-collapsed\">";
        $html .= "<span style='font-weight: normal; font-style: italic'>". htmlspecialchars($this->responseData)."</span>";
        $html .= "</div>";
        $html .= "</th>";

        $html .= "</tr>";
        return $html;
    }
}
