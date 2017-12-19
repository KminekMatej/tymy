<?php

namespace Tapi;
use Nette;
use Nette\Utils\Json;
use Tapi\RequestMethod;
use Tracy\Debugger;

/**
 * Project: tymy_v2
 * @author kminekmatej created on 8.12.2017, 9:48:27
 */

abstract class TapiAbstraction {
    const SESSION_SECTION = "TAPI_SECTION";
    
    /** @var integer ID */
    private $id;
    
    /** @var boolean Is being cached */
    private $cacheable;
    
    /** @var integer Timeout in seconds to drop cache  */
    private $cachingTimeout;
    
    /** @var RequestMethod Url request method */
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
    
    /** @var Nette\Http\Session */
    private $session;
    
    /** @var boolean Should sent data be encoded in JSON */
    private $jsonEncoding;

    /** @var \Tymy\TracyPanelTymy */
    private $tymyPanel;
    
    abstract function init();
    
    abstract function composeUrl();
    
    protected abstract function postProcess();
    
    public function __construct(\App\Model\Supplier $supplier, \App\Model\TapiAuthenticator $tapiAuthenticator, Nette\Security\User $user, Nette\Http\Session $session) {
        $this->initTapiDebugPanel();
        $this->tapiAuthenticator = $tapiAuthenticator;
        $this->supplier = $supplier;
        $this->user = $user;
        $this->session = $session;
        $this->cacheable = TRUE;
        $this->cachingTimeout = CachedResult::TIMEOUT_SMALL;
        $this->jsonEncoding = TRUE;
        $this->dataReady = FALSE;
        $this->tsidRequired = TRUE;
        $this->method = RequestMethod::GET;
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
        if (!$this->dataReady || is_null($this->session))
            return null;
        $sessionSection = $this->session->getSection(self::SESSION_SECTION);
        $sessionSection[$this->getClassCacheName()] = new CachedResult(date("U") + $this->cachingTimeout, $this->data);
    }
    
    public function resetCache(){
        if (is_null($this->session))
            return null;
        $sessionSection = $this->session->getSection(self::SESSION_SECTION);
        unset($sessionSection[$this->getClassCacheName()]);
        return $this;
    }
    
    private function loadFromCache(){
        if (is_null($this->session))
            return null;
        $sessionSection = $this->session->getSection(self::SESSION_SECTION);
        $cachedResult = $sessionSection[$this->getClassCacheName()];
        if($cachedResult == null || !$cachedResult->isValid())
            return null;
        $this->data = $cachedResult->load();
        $this->dataReady = TRUE;
    }
    
    private function requestFromApi($relogin = TRUE){
        $this->composeUrl();
        
        if(is_null($this->url))
            return FALSE;
        
        if($this->tsidRequired)
            $this->setTsid ($this->user->getIdentity()->sessionKey);
        
        //add parameters to url
        if(!is_null($this->requestParameters)){
            $this->url = preg_replace('/\\?.*/', '', $this->url); // firstly try to remove all url params before adding them - important for relogins
            $this->url .= "?" . http_build_query($this->requestParameters);
        }
        
        Debugger::timer("tapi-request" . spl_object_hash($this));
        $ch = curl_init(); 
        curl_setopt($ch, CURLOPT_URL, $this->url); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        
        if($this->method != RequestMethod::GET){
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $this->method);
            if(isset($this->requestData)){
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
                curl_setopt($ch, CURLOPT_POSTFIELDS, $this->jsonEncoding ? json_encode($this->requestData) : $this->requestData);
            }
        }
        
        $curl = curl_exec($ch);
        if($curl === FALSE){
            throw new \Tymy\Exception\APIException("Unknown error while procesing tapi request");
        } else {
            $this->resultStatus = new ResultStatus(Json::decode($curl));
        }
        $curlInfo = curl_getinfo($ch);
        curl_close($ch);
        
        if($this->resultStatus->isValid()){
            $this->data = $this->resultStatus->getData();
            $this->dataReady = TRUE;
            $this->postProcess();
            $this->saveToCache();
        }
        
        $this->tymyPanel->logAPI("TAPI request", $this->url, Debugger::timer("tapi-request" . spl_object_hash($this)));
        
        if($this->resultStatus->isValid()){// tapi request loaded succesfully
            return TRUE;
        }
        
        switch ($curlInfo["http_code"]) {
            case 401: // unauthorized, try to refresh
                if($this->tsidRequired && $relogin){// may be only already invalid TSID, try to obtain new one
                    $newLogin = $this->tapiAuthenticator->reAuthenticate([$this->user->getIdentity()->data["data"]->login, $this->user->getIdentity()->data["hash"]]);
                    $this->user->getIdentity()->sessionKey = $newLogin->result->sessionKey;
                    $this->setTsid($this->user->getIdentity()->sessionKey);
                    $this->requestFromApi(FALSE);
                    return TRUE;
                } 
                return FALSE;
            default:
                Debugger::barDump($this->url);
                Debugger::barDump($this->method);
                Debugger::barDump($this->requestData);
                Debugger::barDump($curlInfo);
                throw new \Tymy\Exception\APIException("Request [".$this->method."] ".$this->url." failed with error code " . $errorData["http_code"]);
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

    public function getMethod() {
        return $this->method;
    }

    public function setCacheable($cacheable) {
        $this->cacheable = $cacheable;
        return $this;
    }

    public function setMethod(RequestMethod $method) {
        $this->method = $method;
        return $this;
    }
    
    public function getCachingTimeout() {
        return $this->cachingTimeout;
    }

    public function getUrl() {
        return $this->url;
    }

    public function setCachingTimeout($cachingTimeout) {
        $this->cachingTimeout = $cachingTimeout;
        return $this;
    }

    public function setUrl($url) {
        $this->url = $this->supplier->getApiRoot() . DIRECTORY_SEPARATOR . $url;
        return $this;
    }

    public function getTsid() {
        return $this->tsid;
    }

    public function setTsid($tsid) {
        $this->tsid = $tsid;
        $this->setRequestParameter("TSID", $this->tsid);
        return $this;
    }

    public function getData() {
        $this->loadFromCache();
        if($this->data == null){
            $this->requestFromApi();
        }
        return $this->data;
    }
    
    protected function getClassCacheName(){
        $className = get_class($this);
        if($this->getId() != null)
            $className .= ":" . $this->getId ();
        return $className;
    }

    protected function timeLoad(&$date) {
        $date = date('c',strtotime("$date UTC"));
        return $date;
    }
    
    protected function timeSave(&$date) {
        $date = gmdate('c',strtotime("$date"));
        return $date;
    }

}
