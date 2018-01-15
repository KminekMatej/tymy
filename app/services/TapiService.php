<?php

namespace Tapi;
use Nette\Utils\JsonException;
use Nette\Security\User;
use Tapi\Exception\APIAuthenticationException;
use Tapi\Exception\APIException;
use App\Model\TapiAuthenticator;
use Tracy\Debugger;
use Nette\Utils\Json;
use Tapi\TracyTapiPanel;
use App\Model\Supplier;

/**
 * Project: tymy_v2
 * Description of TapiService
 *
 * @author kminekmatej created on 7.1.2018, 22:24:46
 */
class TapiService {
    
    /** @var User */
    private $user;
    
    /** @var TapiAuthenticator */
    private $authenticator;
    
    /** @var Supplier */
    private $supplier;
    
    /** @var TracyTapiPanel */
    private $tapiPanel;
    
    /** @var TapiObject */
    private $tapiObject;
    
    /** @var string */
    private $url;
    
    public function __construct(User $user, TapiAuthenticator $authenticator, Supplier $supplier) {
        $this->user = $user;
        $this->authenticator = $authenticator;
        $this->supplier = $supplier;
        $this->tapiObject = NULL;
        $this->initTapiDebugPanel();
    }
    
    private function initTapiDebugPanel(){
        $panelId = "TAPI";
        if(is_null(Debugger::getBar()->getPanel($panelId))){
            $this->tapiPanel = new TracyTapiPanel;
            Debugger::getBar()->addPanel($this->tapiPanel, $panelId);
        } else {
            $this->tapiPanel = Debugger::getBar()->getPanel($panelId);
        }
    }
    
    /**
     * @param \Tapi\TapiObject $tapiObject Object to perform request on
     * @throws APIException
     * @throws APIAuthenticationException
     * @return ResultStatus or NULL on failure;
     */
    public function request(TapiObject $tapiObject){
        $this->tapiObject = $tapiObject;
        $this->composeRequestUrl();
        return $this->performRequest(TRUE);
    }
    
    /**
     * @param \Tapi\TapiObject $tapiObject Object to perform request on
     * @return ResultStatus or NULL on failure;
     */
    public function requestNoRelogin(TapiObject $tapiObject){
        $this->tapiObject = $tapiObject;
        $this->composeRequestUrl();
        return $this->performRequest(FALSE);
    }
    
    private function performRequest($relogin = TRUE) {
        if (is_null($this->supplier->getApiRoot()) || is_null($this->tapiObject) || is_null($this->url))
            throw new APIException("Failure: request input data not set correctly.");

        $curl_response = $this->executeRequest();
        
        if ($curl_response->data === FALSE)
            throw new APIException("Unknown error while procesing tapi request");
        return $this->respond($curl_response->data, $curl_response->info, $relogin);
    }

    private function composeRequestUrl() {
        $fullUrl = $this->getFullUrl();

        $paramArray = $this->tapiObject->getRequestParameters();
        if ($this->tapiObject->getTsidRequired())
            $paramArray["TSID"] = $this->user->getIdentity()->sessionKey;

        //add parameters to url
        if (count($paramArray)) {
            $fullUrl = preg_replace('/\\?.*/', '', $fullUrl); // firstly try to remove all url params before adding them - important for relogins
            $fullUrl .= "?" . http_build_query($paramArray);
        }
        $this->url = $fullUrl;
        return TRUE;
    }
    
    private function getFullUrl(){
        return $this->supplier->getApiRoot() . DIRECTORY_SEPARATOR . $this->tapiObject->getUrl();
    }
    
    private function executeRequest() {
        $objectHash = spl_object_hash($this->tapiObject);
        Debugger::timer("tapi-request $objectHash");
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        if ($this->tapiObject->getMethod() != RequestMethod::GET) {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $this->tapiObject->getMethod());
            if ($this->tapiObject->getRequestData()) {
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
                curl_setopt($ch, CURLOPT_POSTFIELDS, $this->tapiObject->getJsonEncoding() ? json_encode($this->tapiObject->getRequestData()) : $this->tapiObject->getRequestData());
            }
        }
        $result = ["data" => curl_exec($ch), "info" => curl_getinfo($ch)];
        curl_close($ch);
        $this->tapiPanel->logAPI("TAPI request", $this->url, Debugger::timer("tapi-request $objectHash"));
        return (object) $result;
    }

    private function respond($curl_data, $curl_info, $relogin) {
        try {
            $resultStatus = new ResultStatus(Json::decode($curl_data));
        } catch (JsonException $exc) {
            if (!Debugger::$productionMode) {
                Debugger::barDump($curl_info);
                Debugger::barDump($this->url);
                Debugger::barDump($this->tapiObject->getMethod());
                Debugger::barDump($this->tapiObject->getRequestData());
            } else {
                throw new APIException("Unknown error while procesing tapi request");
            }
        }
        
        switch ($curl_info["http_code"]) {
            case 200: //everything ok
                return $this->success($resultStatus, $relogin);
            case 400: 
                throw new APIException("Chyba 400: Neznámý dotaz");
            case 401: // unauthorized, try to refresh
                return $this->loginFailure($relogin);
            case 403: 
                throw new APIException("Chyba 403: Nedostatečná práva");
            default:
                Debugger::barDump($curl_info);
                Debugger::barDump($this->url);
                Debugger::barDump($this->tapiObject->getMethod());
                Debugger::barDump($this->tapiObject->getRequestData());
                throw new APIException("Request [" . $this->tapiObject->getMethod() . "] " . $this->url . " failed with error code " . $curl_info["http_code"]);
        }
    }

    private function loginFailure($relogin) {
        if ($relogin && !is_null($this->authenticator)) { // relogin only if specified, is authenticator and is class needed logins
            $savedTapiObject = $this->tapiObject;
            $newLogin = $this->authenticator->setTapiService($this)->reAuthenticate([$this->user->getIdentity()->data["login"], $this->user->getIdentity()->data["hash"]]);
            $this->user->getIdentity()->sessionKey = $newLogin->sessionKey;
            return $this->requestNoRelogin($savedTapiObject);
        } else {
            throw new APIAuthenticationException("Login failed. Wrong username or password.");
        }
    }
    
    private function success(ResultStatus $resultStatus, $relogin) {
        $data = $resultStatus->getData();
        switch ($resultStatus->getStatus()) {
            case "ERROR":
                switch ($data->statusMessage) { //TODO add some another reasons when they appear
                    case "Not loggged in":
                        return $this->loginFailure($relogin);
                }
                break;
            case "OK":
                return $resultStatus;
            default:
                throw new APIException("API request returned abnormal status " . $data->status . " : " . $data->statusMessage);
        }
    }
}
