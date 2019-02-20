<?php

namespace Tapi;

use App\Model\Supplier;
use App\Model\TapiAuthenticator;
use Nette;
use Nette\Caching\Cache;
use Nette\Caching\Storages\NewMemcachedStorage;
use Nette\Utils\DateTime;
use stdClass;
use Tapi\Exception\APIException;
use Tapi\RequestMethod;
use Tapi\TapiService;

/**
 * Project: tymy_v2
 * @author kminekmatej created on 8.12.2017, 9:48:27
 */

abstract class TapiObject {
    
    const CACHE_STORAGE = "TapiObjects";
    const CACHE_TIMEOUT_NONE = 0; // turn off caching
    const CACHE_TIMEOUT_TINY = 60; // turn off caching
    const CACHE_TIMEOUT_SMALL = 180; // 3 minutes - smallest allowed timeout
    const CACHE_TIMEOUT_MEDIUM = 300; // 5 minutes - medium timeout
    const CACHE_TIMEOUT_LARGE = 600; // 10 minutes - timeout larger than user usually stays on site
    const CACHE_TIMEOUT_DAY = 86400; // one day - for abnormal caching purposes
    
    const BAD_REQUEST = 400;
    
    const DATETIME_ISO8601 = "c";
    const CZECH_DATETIME = "j.n.Y H:i";
    const CZECH_DATE = "j.n.Y";
    const TIME = "H:i:s";
    const TIME_H_M = "H:i";
    const MYSQL_DATE = "Y-m-d";
    const MYSQL_DATETIME = "Y-m-d H:i:s";
    
    /** @var Nette\Security\User */
    protected $user;
    
    /** @var integer ID */
    private $id;
    
    /** @var boolean Is being cached */
    private $cacheable;
    
    /** @var integer Timeout in seconds to drop cache  */
    private $cachingTimeout;
    
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
    protected $requestParameters;
    
    /** @var string Request url */
    private $url;
    
    /** @var Supplier */
    protected $supplier;
    
    /** @var TapiAuthenticator */
    protected $tapiAuthenticator;
    
    /** @var boolean Should sent data be encoded in JSON */
    private $jsonEncoding;
    
    /** @var TapiService */
    protected $tapiService;
    
    /** @var Nette\Caching\Cache */
    protected $cache;
    
    /** @var NewMemcachedStorage */
    protected $cacheStorage;
    
    public abstract function init();
    
    protected abstract function preProcess();
    
    protected abstract function postProcess();
    
    protected function globalInit(){
        $this->setId(NULL);
        $this->requestParameters = [];
        $this->requestData = NULL;
        $this->dataReady = FALSE;
    }
    
    public function __construct(Supplier $supplier,  Nette\Security\User $user = NULL, TapiService $tapiService = NULL, NewMemcachedStorage $cacheStorage = NULL) {
        if ($cacheStorage) {
            $this->cacheStorage = $cacheStorage;
            $this->cache = new Cache($cacheStorage, TapiObject::CACHE_STORAGE);
        }
        $this->supplier = $supplier;
        if($user) $this->user = $user;
        $this->cacheable = TRUE;
        $this->cachingTimeout = TapiObject::CACHE_TIMEOUT_SMALL;
        if($tapiService) $this->tapiService = $tapiService;
        $this->jsonEncoding = TRUE;
        $this->tsidRequired = TRUE;
        $this->method = RequestMethod::GET;
        $this->options = new stdClass();
        $this->options->warnings = 0;
        $this->init();
    }
    
    public function saveToCache() {
        if (!$this->dataReady || !$this->cacheable)
            return null;
        $key = $this->getCacheKey();
        $save = $this->cache->save($key, ["data" => $this->data, "options" => $this->options], [Cache::EXPIRE => $this->cachingTimeout . ' seconds', Cache::TAGS => [$this->getCacheObjectTag(), $this->getCacheUserTag()]]);
        $allKeys = $this->cache->load("allkeys");
        $allKeys[$key] = $key;
        
        $this->cache->save("allkeys", $allKeys, [Cache::EXPIRE => self::CACHE_TIMEOUT_DAY . ' seconds']);
    }
    
    public function cleanCache(){
        $this->cache->clean([Cache::TAGS => [$this->getCacheUserTag()]]);
        return $this;
    }
    
    public function cleanCompleteCache(){
        $this->cache->clean([Cache::ALL => TRUE]);
        return $this;
    }
    
    private function loadFromCache(){
        $data = $this->cache->load($this->getCacheKey());
        if($data != null){
            $this->data = $data["data"];
            $this->options = $data["options"];
            $this->options->isFromCache = TRUE;
            $this->dataReady = TRUE;
        }
    }

    public function getCacheUserTag() {
        return $this->supplier->getTym() . "@" . $this->user->getId();
    }

        /**
     * @param type $relogin
     * @return ResultStatus
     * @throws APIException
     */
    protected function requestFromApi($relogin = TRUE) {
        $resultStatus = $relogin ? $this->tapiService->request($this) : $this->tapiService->requestNoRelogin($this);
        if ($resultStatus->isValid()) {
            $this->data = $resultStatus->getData();
            $this->options->isFromCache = FALSE;
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
    
    /**
     * @param bool $forceRequest
     * @throws APIException
     */
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
    
    public function setWarnings($warnings) {
        $this->options->warnings = $warnings;
        return $this;
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
    
    protected function getCacheObjectTag(){
        return $this->supplier->getTym() . "@" . $this->getMethod() . ":" . $this->getUrl();
    }
    
    protected function getCacheKey($key_override = NULL){
        if($this->getUrl() == NULL) throw new APIException("No url to save");
        $key = $this->user->getId() . ":";
        if(!is_null($key_override)) $key .= $key_override;
        else $key .= $this->getCacheObjectTag() . ($this->requestParameters ? "?" . http_build_query($this->requestParameters) : "");
        return $key;
    }

    protected function timeLoad(&$date) {
        $date = date('c',strtotime("$date UTC"));
        return $date;
    }
    
    protected function timeSave(&$date) {
        $dt = DateTime::createFromFormat(TapiObject::MYSQL_DATETIME, $date);
        if($dt === FALSE) $dt = DateTime::createFromFormat(TapiObject::CZECH_DATETIME, $date);
        if($dt === FALSE) $dt = new DateTime($date);
        if($dt){
            $date = gmdate('c', $dt->format("U"));
            return $date;
        }
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
    
    public function setSupplier(Supplier $supplier) {
        $this->supplier = $supplier;
        return $this;
    }
    
}
