<?php

namespace Tapi;
use Nette;
use Nette\Utils\Json;
use Tapi\RequestMethod;
use Tracy\Debugger;
use Tymy\Exception\APIException;

/**
 * Project: tymy_v2
 * @author kminekmatej created on 8.12.2017, 9:48:27
 */

abstract class TapiObject {
    
    /** @var Nette\Security\User */
    protected $user;
    
    /** @var integer ID */
    private $id;
    
    /** @var boolean Is being cached */
    private $cacheable;
    
    /** @var integer Timeout in seconds to drop cache  */
    private $cachingTimeout;
    
    /**  @var CacheService */
    protected $cacheService;
    
    /** @var string Url request method */
    private $method;
    
    /** @var ResultStatus Tapi result status */
    private $resultStatus;
    
    /** @var string */
    private $tsid;
    
    /** @var boolean TSID is required in this resource */
    private $tsidRequired;
    
    /** @var boolean Tapi is ready */
    private $dataReady;
    
    /** @var object data returned by request */
    protected $data;
    
    /** @var object options returned by request */
    protected $options;
    
    /** @var object data to be sent along with request */
    private $requestData;
    
    /** @var array(String) Request parameters to be sent along with request */
    private $requestParameters;
    
    /** @var string Request url */
    private $url;
    
    /** @var \App\Model\Supplier */
    protected $supplier;
    
    /** @var \App\Model\TapiAuthenticator */
    protected $tapiAuthenticator;
    
    /** @var boolean Should sent data be encoded in JSON */
    private $jsonEncoding;

    /** @var \Tymy\TracyPanelTymy */
    private $tymyPanel;
    
    abstract function init();
    
    protected abstract function preProcess();
    
    protected abstract function postProcess();
    
    public function __construct(\App\Model\Supplier $supplier, \App\Model\TapiAuthenticator $tapiAuthenticator, Nette\Security\User $user, CacheService $cacheService) {
        $this->initTapiDebugPanel();
        $this->tapiAuthenticator = $tapiAuthenticator;
        $this->supplier = $supplier;
        $this->user = $user;
        $this->cacheable = TRUE;
        $this->cachingTimeout = CacheService::TIMEOUT_SMALL;
        $this->cacheService = $cacheService;
        $this->jsonEncoding = TRUE;
        $this->dataReady = FALSE;
        $this->tsidRequired = TRUE;
        $this->method = RequestMethod::GET;
        $this->options = new \stdClass();
        $this->options->warnings = 0;
        $this->init();
    }
    
    protected function initTapiDebugPanel(){
        $panelId = "TymyAPI";
        if(is_null(Debugger::getBar()->getPanel($panelId))){
            $this->tymyPanel = new \Tymy\TracyPanelTymy;
            Debugger::getBar()->addPanel($this->tymyPanel, $panelId);
        } else {
            $this->tymyPanel = Debugger::getBar()->getPanel($panelId);
        }
    }
    
    private function saveToCache() {
        if (!$this->dataReady || !$this->cacheable)
            return null;
        $this->cacheService->save($this->getClassCacheName(), $this->cachingTimeout, $this->data, $this->options);
    }
    
    public function resetCache(){
        $this->cacheService->clear($this->getClassCacheName());
        return $this;
    }
    
    private function loadFromCache(){
        $data = $this->cacheService->load($this->getClassCacheName());
        if(is_null($data)) return null;
        else {
            $this->data = $data->getData();
            $this->options = $data->getOptions();
            $this->dataReady = TRUE;
        }
    }

    private function requestFromApi($relogin = TRUE){
        if(is_null($this->supplier->getApiRoot()) || is_null($this->getUrl()))
            return FALSE;
        
        $url = $this->getFullUrl();
        
        $paramArray = $this->requestParameters;
        if($this->tsidRequired) $paramArray["TSID"] = $this->user->getIdentity()->sessionKey;
        
        //add parameters to url
        if(count($paramArray)){
            $url = preg_replace('/\\?.*/', '', $url); // firstly try to remove all url params before adding them - important for relogins
            $url .= "?" . http_build_query($paramArray);
        }
        
        Debugger::timer("tapi-request" . spl_object_hash($this));
        $ch = curl_init(); 
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        
        if($this->getMethod() != RequestMethod::GET){
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $this->getMethod());
            if(isset($this->requestData)){
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
                curl_setopt($ch, CURLOPT_POSTFIELDS, $this->jsonEncoding ? json_encode($this->requestData) : $this->requestData);
            }
        }
        
        $curl = curl_exec($ch);
        if($curl === FALSE){
            throw new APIException("Unknown error while procesing tapi request");
        } else {
            try {
                $this->resultStatus = new ResultStatus(Json::decode($curl));
            } catch (Nette\Utils\JsonException $exc) {
                if(!Debugger::$productionMode){
                    Debugger::barDump($this->method, "CURL method");
                    Debugger::barDump($url, "CURL URL");
                    Debugger::barDump($this->jsonEncoding ? json_encode($this->requestData) : $this->requestData, "CURL Data");
                } else {
                    throw new APIException("Unknown error while procesing tapi request");
                }
            }
        }
        $curlInfo = curl_getinfo($ch);
        curl_close($ch);
        
        if($this->resultStatus->isValid()){
            $this->data = $this->resultStatus->getData();
            $this->dataReady = TRUE;
            $this->postProcess();
            $this->saveToCache();
        }
        
        $this->tymyPanel->logAPI("TAPI request", $url, Debugger::timer("tapi-request" . spl_object_hash($this)));
        
        if($this->resultStatus->isValid()){// tapi request loaded succesfully
            return TRUE;
        }
        
        switch ($curlInfo["http_code"]) {
            case 401: // unauthorized, try to refresh
                if($this->tsidRequired && $relogin){// may be only already invalid TSID, try to obtain new one
                    $newLogin = $this->tapiAuthenticator->reAuthenticate([$this->user->getIdentity()->data["data"]->login, $this->user->getIdentity()->data["hash"]]);
                    $this->user->getIdentity()->sessionKey = $newLogin->result->sessionKey;
                    $this->requestFromApi(FALSE);
                    return TRUE;
                } 
                return FALSE;
            default:
                Debugger::barDump($url);
                Debugger::barDump($this->method);
                Debugger::barDump($this->requestData);
                Debugger::barDump($curlInfo);
                throw new APIException("Request [".$this->method."] $url failed with error code " . $errorData["http_code"]);
        }
    }
    
    protected function setRequestParameter($key, $value) {
        $this->requestParameters[$key] = $value;
    }

    //GETTERS AND SETTERS
    
    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
        return $this;
    }
    
    public function isCacheable() {
        return $this->cacheable;
    }

    public function setCacheable($cacheable) {
        $this->cacheable = $cacheable;
        return $this;
    }
    
    protected function getMethod() {
        return $this->method;
    }

    protected function setMethod($method) {
        $this->method = $method;
        return $this;
    }
    
    protected function getCachingTimeout() {
        return $this->cachingTimeout;
    }

    protected function setCachingTimeout($cachingTimeout) {
        $this->cachingTimeout = $cachingTimeout;
        return $this;
    }
    
    private function getFullUrl(){
        return $this->supplier->getApiRoot() . DIRECTORY_SEPARATOR . $this->getUrl();
    }

    public function getUrl() {
        return $this->url;
    }

    public function setUrl($url) {
        $this->url = $url;
        return $this;
    }
    
    public function getData($forceRequest = FALSE) {
        $this->preProcess();
        if($this->cacheable){
            $this->loadFromCache();
        }
        if($this->data == null || $forceRequest || !$this->cacheable){
            $this->requestFromApi();
        }
        return $this->data;
    }
    
    public function getWarnings() {
        return $this->options->warnings;
    }
    
    /**
     * An alias for getData() function. Used in actions.
     */
    public function perform($forceRequest = FALSE){
        return $this->getData($forceRequest);
    }
    
    protected function getRequestData() {
        return $this->requestData;
    }

    protected function setRequestData($requestData) {
        $this->requestData = $requestData;
        return $this;
    }
    
    private function getClassCacheName(){
        if($this->getUrl() == NULL) throw new \Exception ("No url to save");
        return $this->getMethod() . ":" . $this->getUrl() . ($this->requestParameters ? "?" . http_build_query($this->requestParameters) : "");
    }

    protected function timeLoad(&$date) {
        $date = date('c',strtotime("$date UTC"));
        return $date;
    }
    
    protected function timeSave(&$date) {
        $date = gmdate('c',strtotime("$date"));
        return $date;
    }
    
    protected function getTsidRequired() {
        return $this->tsidRequired;
    }

    protected function setTsidRequired($tsidRequired) {
        $this->tsidRequired = $tsidRequired;
        return $this;
    }

    protected function getJsonEncoding() {
        return $this->jsonEncoding;
    }

    protected function setJsonEncoding($jsonEncoding) {
        $this->jsonEncoding = $jsonEncoding;
        return $this;
    }
    
    public function getOptions() {
        return $this->options;
    }

    public function setOptions($options) {
        $this->options = $options;
        return $this;
    }

}
