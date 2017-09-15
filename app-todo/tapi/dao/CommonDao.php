<?php

namespace Tapi;

/**
 * Description of CommonDao
 *
 * @author kminekmatej
 */
abstract class CommonDao {
    
    private $url;
    private $urlParams;
    private $method;
    private $tsidRequired;
    
    private $result;
    private $postData;
    
    /** @var \App\Model\Supplier */
    protected $supplier;
    /** @var \Tymy\TracyPanelTymy */
    protected $tymyPanel;
    /** @var Nette\Http\Session */
    protected $session;
    /** @var Nette\Security\User */
    protected $user;
    /** @var \Tymy\TracyPanelTymy */
    protected $tymyPanel;
    /** @var \App\Model\TapiAuthenticator */
    protected $tapiAuthenticator;
    
    abstract function getFindUrl($id);
    abstract function respond();
    
    public function __construct(\App\Model\Supplier $supplier, \App\Model\TapiAuthenticator $tapiAuthenticator, Nette\Security\User $user, Nette\Http\Session $session) {
        $this->tapiAuthenticator = $tapiAuthenticator;
        $this->supplier = $supplier;
        $this->user = $user;
        $this->session = $session;
        $this->setUp();
    }
    
    protected function urlEnd() {
        
        $this->fullUrl = preg_replace('/\\?.*/', '', $this->fullUrl); // firstly try to remove all url params before adding them - important for relogins
        $this->fullUrl .= "/" . $this->composeUriParams();
        return $this;
    }
    
    protected function setUp(){
        $this->initTapiDebugPanel();
        $this->url = $this->supplier->getApiRoot();
        $this->url .= DIRECTORY_SEPARATOR;
        
    }
    
    protected function tearDown(){
        
    }

    public function find($id) {
        $this->method = "GET";
        $this->url = $this->getFindUrl($id);
        $this->request();
        return $this->respond();
    }
    
    protected function request($relogin = TRUE){
        if($this->getTSIDRequired()){
            $this->setUrlParam("TSID", $this->user->getIdentity()->sessionKey);
        }
        \Tracy\Debugger::timer("tapi-request" . spl_object_hash($this));
        $ch = curl_init(); 
        curl_setopt($ch, CURLOPT_URL, $this->url); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        
        if($this->method != "GET"){
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $this->method);
        }
        
        if(in_array($this->method, ["POST","PUT"]) && isset($this->postData) && is_array($this->postData)){
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($this->postData));
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json')
            );
        }
        $jsonResponse = curl_exec($ch);
        $curlInfo = curl_getinfo ($ch);
        curl_close($ch);
        $this->tymyPanel->logAPI("TAPI request", $this->url, \Tracy\Debugger::timer("tapi-request" . spl_object_hash($this)));
        
        if ($jsonResponse !== FALSE) {
            switch ($curlInfo["http_code"]) {
                case 200: // api request loaded succesfully
                    return $this->apiResponse(Json::decode($jsonResponse), $relogin);
                case 401: // unauthorized, try to refresh
                    return $this->loginFailure($relogin);
                case 403: // forbidden, return the error message
                    $forbidden = Json::decode($jsonResponse);
                    $tapiMSG = $jsonResponse ? Json::decode($jsonResponse)->statusMessage : "403 Forbidden";
                    throw new \Tymy\Exception\APIException($tapiMSG);
                case 400: // bad request, throw error
                    $tapiMSG = $jsonResponse ? Json::decode($jsonResponse)->statusMessage : "400 Bad request";
                    throw new \Tymy\Exception\APIException($tapiMSG);
                case 500: // error 500 can display when logging out on unlogged account, so this is temporary solution
                    $tapiMSG = $jsonResponse ? Json::decode($jsonResponse)->statusMessage : "500 Internal Server Error";
                    throw new \Tymy\Exception\APIException($tapiMSG);
                default:
                    $tapiMSG = $jsonResponse ? Json::decode($jsonResponse)->statusMessage : $curlInfo["http_code"] . " Unknown error";
                    throw new \Tymy\Exception\APIException($tapiMSG);
            }
        } else {
            throw new \Tymy\Exception\APIException("TAPI query failed for unknown reason");
        }
    }
    
    public function getResult() {
        return $this->result;
    }

    public function getData() {
        return $this->data;
    }

    public function setResult($result) {
        $this->result = $result;
        return $this;
    }

    public function setData($data) {
        $this->data = $data;
        return $this;
    }
    
    private function initTapiDebugPanel(){
        $panelId = "TymyAPI";
        if(is_null(\Tracy\Debugger::getBar()->getPanel($panelId))){
            $this->tymyPanel = new \Tymy\TracyPanelTymy;
            \Tracy\Debugger::getBar()->addPanel($this->tymyPanel, $panelId);
        } else {
            $this->tymyPanel = \Tracy\Debugger::getBar()->getPanel($panelId);
        }
    }
    
    public function getTsidRequired() {
        return $this->tsidRequired;
    }

    public function setTsidRequired($tsidRequired) {
        $this->tsidRequired = $tsidRequired;
        return $this;
    }
    
    public function setUrlParam($param, $value) {
        $this->urlParams[$param] = $value;
        return $this;
    }

    private function loginFailure($relogin) {
        if ($relogin && !is_null($this->tapiAuthenticator)) { // relogin only if specified, is authenticator and is class needed logins
            $newLogin = $this->tapiAuthenticator->reAuthenticate([$this->user->getIdentity()->data["data"]->login, $this->user->getIdentity()->data["hash"]]);
            $this->user->getIdentity()->sessionKey = $newLogin->result->sessionKey;
            $this->request(FALSE);
            return $this->respond();
        } else {
            throw new \Tymy\Exception\APIAuthenticationException("Login failed. Wrong username or password.");
        }
    }



}
