<?php

namespace Tapi;
use Nette;
use Tapi\RequestMethod;
use Tracy\Debugger;
use Tapi\Exception\APIException;
use Tapi\TapiService;

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
    
    /** @var bool TSID is required in this resource */
    private $tsidRequired;
    
    /** @var bool Tapi is ready */
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
    
    /** @var TapiService */
    protected $tapiService;
    
    protected abstract function init();
    
    protected abstract function preProcess();
    
    protected abstract function postProcess();
    
    public function __construct(\App\Model\Supplier $supplier,  Nette\Security\User $user = NULL, CacheService $cacheService = NULL, TapiService $tapiService = NULL) {
        $this->supplier = $supplier;
        if($user) $this->user = $user;
        $this->cacheable = TRUE;
        $this->cachingTimeout = CacheService::TIMEOUT_SMALL;
        if($cacheService) $this->cacheService = $cacheService;
        if($tapiService) $this->tapiService = $tapiService;
        $this->jsonEncoding = TRUE;
        $this->dataReady = FALSE;
        $this->tsidRequired = TRUE;
        $this->method = RequestMethod::GET;
        $this->options = new \stdClass();
        $this->options->warnings = 0;
        $this->init();
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
        if($data != null){
            $this->data = $data->getData();
            $this->options = $data->getOptions();
            $this->dataReady = TRUE;
        }
    }

    protected function requestFromApi($relogin = TRUE) {
        $resultStatus = $relogin ? $this->tapiService->request($this) : $this->tapiService->requestNoRelogin($this);
        if ($resultStatus->isValid()) {
            $this->data = $resultStatus->getData();
            $this->dataReady = TRUE;
            $this->postProcess();
            $this->saveToCache();
        } else {
            throw new APIException($resultStatus->getMessage());
        }
        return $resultStatus;
    }

    protected function setRequestParameter($key, $value) {
        $this->requestParameters[$key] = $value;
    }

    //GETTERS AND SETTERS
    
    public function getRequestParameters() {
        return $this->requestParameters;
    }
        
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
    
    public function getMethod() {
        return $this->method;
    }

    public function setMethod($method) {
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
    
    public function getUrl() {
        return $this->url;
    }

    public function setUrl($url) {
        $this->url = $url;
        return $this;
    }
    
    public function getData($forceRequest = FALSE) {
        $this->preProcess();
        $this->dataReady = FALSE;
        if($this->cacheable){
            $this->loadFromCache();
        }
        if(!$this->dataReady || $forceRequest || !$this->cacheable){
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
    
    public function getRequestData() {
        return $this->requestData;
    }

    public function setRequestData($requestData) {
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
    
    public function getTsidRequired() {
        return $this->tsidRequired;
    }

    public function setTsidRequired($tsidRequired) {
        $this->tsidRequired = $tsidRequired;
        return $this;
    }

    public function getJsonEncoding() {
        return $this->jsonEncoding;
    }

    public function setJsonEncoding($jsonEncoding) {
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
