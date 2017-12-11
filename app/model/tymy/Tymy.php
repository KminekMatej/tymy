<?php

namespace Tymy;

use Nette;
use Nette\Utils\Json;
use Tracy\Debugger;
use \Datetime;
use DateTimeZone;

/**
 * Description of Tymy
 *
 * @author matej
 */
abstract class Tymy extends Nette\Object{
    
    const SESSION_SECTION = "TAPI";
    
    protected $result = NULL;
    /** @var \App\Presenters\SecuredPresenter */
    protected $presenter;
    /**
     * recId - root id of record (discussion id, event id, ...)
     * $recId integer 
     */
    protected $recId;
    protected $fullUrl;
    /** @var Nette\Security\User */
    protected $user;
    
    private $uriParams;
    private $postData;
    private $jsonEncoding = TRUE;
    /** @var \App\Model\Supplier */
    protected $supplier;
    /** @var \Tymy\TracyPanelTymy */
    protected $tymyPanel;
    /** @var Nette\Http\Session */
    protected $session;
    
    /** @var \App\Model\TapiAuthenticator */
    protected $tapiAuthenticator;
    
    protected $tsid;
    
    protected $method;
    
    /** Function to return full URI of select api */
    abstract protected function select();
    
    /** Function to process after the result from API is obtained, used mainly for formatting or adding new properties to TAPI result */
    abstract protected function postProcess();
    
    /** Function to return TAPI name of this request */
    public function getTapiName(){
        $c = get_class( $this );
        return $c::TAPI_NAME;
    }
    
    /** Function to return if this TAPI class needs TSID for work */
    public function getTSIDRequired(){
        $c = get_class( $this );
        return $c::TSID_REQUIRED;
    }
    
    public function __construct(\App\Model\Supplier $supplier, \App\Model\TapiAuthenticator $tapiAuthenticator, Nette\Security\User $user, Nette\Http\Session $session) {
        $this->initTapiDebugPanel();
        $this->tapiAuthenticator = $tapiAuthenticator;
        $this->supplier = $supplier;
        $this->user = $user;
        $this->session = $session;
        $this->method = "GET";
    }
    
    protected function initTapiDebugPanel(){
        $panelId = "TymyAPI";
        if(is_null(\Tracy\Debugger::getBar()->getPanel($panelId))){
            $this->tymyPanel = new \Tymy\TracyPanelTymy;
            \Tracy\Debugger::getBar()->addPanel($this->tymyPanel, $panelId);
        } else {
            $this->tymyPanel = \Tracy\Debugger::getBar()->getPanel($panelId);
        }
    }
        
    public function setSupplier(\App\Model\Supplier $supplier) {
        $this->supplier = $supplier;
        return $this;
    }

    public function setUser($user) {
        $this->user = $user;
        return $this;
    }

    protected function setUriParam($key, $value) {
        $this->uriParams[$key] = $value;
    }
    
    public function setJsonEncoding($jsonEncoding) {
        $this->jsonEncoding = $jsonEncoding;
        return $this;
    }

    public function getJsonEncoding() {
        return $this->jsonEncoding;
    }
  
    protected function composeUriParams(){
        if(is_null($this->uriParams))
            return "";
        return "?" . http_build_query($this->uriParams);
    }
    
    public function reset(){
        $this->uriParams = NULL;
        $this->tsid = NULL;
        $this->result = NULL;
        $this->recId = NULL;
        $this->fullUrl = NULL;
        $this->postData = NULL;
        $this->method = "GET";
        $this->jsonEncoding = TRUE;
        return $this;
    }
    
    protected function urlStart() {
        $this->fullUrl = $this->supplier->getApiRoot();
        $this->fullUrl .= DIRECTORY_SEPARATOR;
        return $this;
    }
    
    protected function urlEnd() {
        if($this->getTSIDRequired()){
            $this->setTsid($this->user->getIdentity()->sessionKey);
        }
        $this->fullUrl = preg_replace('/\\?.*/', '', $this->fullUrl); // firstly try to remove all url params before adding them - important for relogins
        $this->fullUrl .= "/" . $this->composeUriParams();
        return $this;
    }

    /**
     * @throws \Tymy\Exception\APIException when something goes wrong
     * @return type
     */
    public function fetch(){
        $this->urlStart();

        $this->select();

        $this->urlEnd();
        
        $this->result = $this->execute();
        
        $data = $this->getData();

        $this->postProcess();
        
        return $data;
    }
    
    public function getUriParams(){
        return $this->uriParams;
    }
    
    public function getPostData(){
        return $this->postData;
    }
    
    public function getRecId(){
        return $this->recId;
    }
    
    /**
     * @throws \Tymy\Exception\APIException when something goes wrong
     * @param bool $force Force tapi request again
     * @return data
     */
    public function getData($force = FALSE){
        if (!is_null($this->session)) {
            $sessionSection = $this->session->getSection(self::SESSION_SECTION);
            if (!$force && array_key_exists($this->getTapiName(), $sessionSection)) {
                return $sessionSection[$this->getTapiName()]->data;
            }
        }

        if(is_null($this->result) || $force){
            $this->fetch();
        }
        if(!property_exists($this->result, "data")){
            return null;
        }
        return $this->result->data;
    }
    
    /**
     * @throws \Tymy\Exception\APIException when something goes wrong 
     * @param bool $force Force tapi request again
     * @return result
     */
    public function getResult($force = FALSE){
        if (!is_null($this->session)) {
            $sessionSection = $this->session->getSection(self::SESSION_SECTION);
            if (!$force && array_key_exists($this->getTapiName(), $sessionSection)) {
                return $sessionSection[$this->getTapiName()];
            }
        }

        if(is_null($this->result) || $force){
            $this->fetch();
        }
        
        return $this->result;
    }
    
    /**
     * @param bool $relogin TRUE if after unsuccesfull request should be performed relogin to obtain new TSID
     * @return object containing the response
     * @throws \Tymy\Exception\APIException when something goes wrong
     */
    protected function execute($relogin = TRUE) {
        $contents = $this->request($this->fullUrl);
        if ($contents->status) {
            switch ($contents->curlInfo["http_code"]) {
                case 200: // api request loaded succesfully
                    return $this->apiResponse(Json::decode($contents->result), $relogin);
                case 401: // unauthorized, try to refresh
                    return $this->loginFailure($relogin);
                case 403: // forbidden, return the error message
                    $tapiMSG = $contents->result ? Json::decode($contents->result)->statusMessage : "403 Forbidden";
                    throw new \Tymy\Exception\APIException($tapiMSG);
                case 400: // bad request, throw error
                    throw new \Tymy\Exception\APIException("400 Bad request");
                case 500: // error 500 can display when logging out on unlogged account, so this is temporary solution
                    Debugger::barDump($contents->curlInfo["url"]);
                    Debugger::barDump($this->method);
                    Debugger::barDump($this->postData);
                    Debugger::barDump($this);
                    $tapiMSG = $contents->result ? Json::decode($contents->result)->statusMessage : "500 Internal Server Error";
                    throw new \Tymy\Exception\APIException($tapiMSG);
                default:
                    Debugger::barDump($contents->curlInfo["url"]);
                    Debugger::barDump($this->method);
                    Debugger::barDump($this->postData);
                    Debugger::barDump($this);
                    $tapiMSG = $contents->result ? Json::decode($contents->result)->statusMessage : $contents->curlInfo["http_code"] . " Unknown error";
                    throw new \Tymy\Exception\APIException($tapiMSG);
            }
        } else {
            throw new \Tymy\Exception\APIException("TAPI query failed for unknown reason");
        }
    }
    
    public function getFullUrl() {
        return $this->fullUrl;
    }

    public function getMethod() {
        return $this->method;
    }

    private function apiResponse($response, $relogin) {
        switch ($response->status) {
            case "ERROR":
                switch ($response->statusMessage) { //TODO add some another reasons when they appear
                    case "Not loggged in":
                        return $this->loginFailure($relogin);
                }
                break;
            case "OK":
                $this->result = (object) $response;
                return $this->result;
            default:
                throw new \Tymy\Exception\APIException("API request " . $this->fullUrl . " returned abnormal status " . $response->status . " : " . $response->statusMessage);
        }
    }

    private function loginFailure($relogin) {
        if ($relogin && !is_null($this->tapiAuthenticator)) { // relogin only if specified, is authenticator and is class needed logins
            $newLogin = $this->tapiAuthenticator->reAuthenticate([$this->user->getIdentity()->data["data"]->login, $this->user->getIdentity()->data["hash"]]);
            $this->user->getIdentity()->sessionKey = $newLogin->result->sessionKey;
            $this->setTsid($this->user->getIdentity()->sessionKey);
            $this->urlEnd();
            return $this->execute(FALSE);
        } else {
            throw new \Tymy\Exception\APIAuthenticationException("Login failed. Wrong username or password.");
        }
    }

    protected function timeLoad(&$date) {
        $date = date('c',strtotime("$date UTC"));
        return $date;
    }
    
    protected function timeSave(&$date) {
        $date = gmdate('c',strtotime("$date"));
        return $date;
    }

    protected function request($url) {
        \Tracy\Debugger::timer("tapi-request" . spl_object_hash($this));
        $ch = curl_init(); 
        curl_setopt($ch, CURLOPT_URL, $url); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        
        if($this->method != "GET"){
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $this->method);
        }
        
        if(in_array($this->method, ["POST","PUT","DELETE"]) && isset($this->postData)){
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            curl_setopt($ch, CURLOPT_POSTFIELDS, $this->jsonEncoding ? json_encode($this->postData) : $this->postData);
        }
        $result = curl_exec($ch);
        $output = new \stdClass();
        $output->status = $result === FALSE ? FALSE : TRUE;
        $output->result = $result;
        $output->curlInfo = curl_getinfo ($ch);
        $output->curlError = curl_error ($ch);
        curl_close($ch);
        $this->tymyPanel->logAPI("TAPI request", $this->fullUrl, \Tracy\Debugger::timer("tapi-request" . spl_object_hash($this)));
        return $output;
    }
    
    /**
     * Set post data to send with request
     * @param $data Data to be set
     * @return $this
     */
    protected function setPostData($data){
        $this->postData = $data;
        return $this;
    }
    
    public function recId($recId){
        $this->recId = $recId;
        return $this;
    }
    
    public function getTsid() {
        return $this->tsid;
    }

    public function setTsid($tsid) {
        $this->tsid = $tsid;
        $this->setUriParam("TSID", $tsid);
        return $this;
    }

}
