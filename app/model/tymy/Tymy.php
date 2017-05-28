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
    
    const TYMY_API = ".tymy.cz/api/";
    
    protected $result = NULL;
    protected $protocol;
    /** @var \App\Presenters\SecuredPresenter */
    protected $presenter;
    protected $team;
    /**
     * recId - root id of record (discussion id, event id, ...)
     * $recId integer 
     */
    protected $recId;
    protected $fullUrl;
    protected $user;
    private $uriParams;
    private $postParams;
    /** @var \Tymy\TracyPanelTymy */
    protected $tymyPanel;
    
    /** @var \App\Model\TymyUserManager */
    protected $tapiAuthenticator;
    
    /** Function to return full URI of select api */
    abstract protected function select();
    
    /** Function to process after the result from API is obtained, used mainly for formatting or adding new properties to TAPI result */
    abstract protected function postProcess();
    
    public function __construct(\App\Model\TymyUserManager $tapiAuthenticator = NULL, Nette\Application\UI\Presenter $presenter = NULL) {
        $panelId = "TymyAPI";
        if(is_null(\Tracy\Debugger::getBar()->getPanel($panelId))){
            $this->tymyPanel = new \Tymy\TracyPanelTymy;
            \Tracy\Debugger::getBar()->addPanel($this->tymyPanel, $panelId);
        } else {
            $this->tymyPanel = \Tracy\Debugger::getBar()->getPanel($panelId);
        }
        $this->tapiAuthenticator = $tapiAuthenticator;
        if($presenter != NULL)
            $this->presenter ($presenter);
        $this->https(FALSE);
    }
    
    public function presenter(Nette\Application\UI\Presenter $presenter){
        $this->presenter = $presenter;
        $this->user = $presenter->getUser();
        $this->team($this->user->getIdentity()->data["tym"]);
        $this->setUriParam("TSID", $this->user->getIdentity()->data["sessionKey"]);
        return $this;
    }
    
    public function team($team){
        $this->team = $team;
        return $this;
    }

    protected function setUriParam($key, $value) {
        $this->uriParams[$key] = $value;
    }
    
    protected function composeUriParams(){
        if(is_null($this->uriParams))
            return "";
        return "?" . http_build_query($this->uriParams);
    }
    
    protected function urlStart() {
        $this->fullUrl = $this->protocol;
        if (!isset($this->team))
            throw new \Tymy\Exception\APIException('Team not set!');

        $this->fullUrl .= "://" . $this->team . self::TYMY_API;
        rtrim($this->fullUrl, "/");
        return $this;
    }
    
    protected function urlEnd() {
        $this->fullUrl = preg_replace('/\\?.*/', '', $this->fullUrl); // firstly try to remove all url params before adding them - important for relogins
        $this->fullUrl .= "/" . $this->composeUriParams();
        return $this;
    }

    public function fetch(){
        
        $this->urlStart();

        $this->select();

        $this->urlEnd();
        
        try {
            $this->result = $this->execute();
        } catch (\Tymy\Exception\APIAuthenticationException $exc) {
            $this->user->logout(true);
            $this->presenter->flashMessage('You have been signed out due to inactivity. Please sign in again.');
            $this->presenter->redirect('Sign:in', ['backlink' => $this->presenter->storeRequest()]);
        }
        
        
        $data = $this->getData();

        $this->postProcess();
        
        return $data;
    }
    
    public function getUriParams(){
        return $this->uriParams;
    }
    
    public function getPostParams(){
        return $this->postParams;
    }
    
    public function getProtocol(){
        return $this->protocol;
    }
    
    public function getRecId(){
        return $this->recId;
    }
    
    public function getTeam(){
        return $this->team;
    }
    
    public function getData(){
        return isset($this->result) ? $this->result->data : NULL;
    }
    
    public function getResult(){
        return isset($this->result) ? $this->result : NULL;
    }
    
    protected function execute($relogin = TRUE) {
        $contents = $this->request($this->fullUrl);
        if ($contents->status) {
            if ($contents->curlInfo["http_code"] == 401) { // login failed, try to refresh
                return $this->loginFailure($relogin);
            }
            
            if ($contents->curlInfo["http_code"] != 200) {
                throw new \Tymy\Exception\APIException("API request ". $this->fullUrl ." retuned wrong error code " . $contents->curlInfo["http_code"]);
            }
            $jsonObj = Json::decode($contents->result);
            
            if ($jsonObj->status == "ERROR" && $jsonObj->statusMessage == "Not loggged in") {
                return $this->loginFailure($relogin);
            }
            
            if ($jsonObj->status != "OK") {
                throw new \Tymy\Exception\APIException("API request ". $this->fullUrl ." returned abnormal status " . $jsonObj->status . " : " . $jsonObj->statusMessage);
            }
            
            $this->result = (object) $jsonObj;

            return $this->result;
        } else {
            throw new \Tymy\Exception\APIException("Nastala neošetřená výjimka ve funkci Tymy->execute(). Prosím kontaktujte vývojáře.");
        }
    }
    
    private function loginFailure($relogin) {
        if ($relogin && !is_null($this->tapiAuthenticator)) {
            $newLogin = $this->tapiAuthenticator->reAuthenticate([$this->user->getIdentity()->data["data"]->login, $this->user->getIdentity()->data["hash"]]);
            $this->user->getIdentity()->sessionKey = $newLogin->result->sessionKey;
            $this->setUriParam("TSID", $this->user->getIdentity()->sessionKey);
            $this->urlEnd();
            return $this->execute(FALSE);
        } else {
            throw new \Tymy\Exception\APIAuthenticationException("API request " . $this->fullUrl . " retuned error 401 - Not logged in");
        }
    }

    protected function timezone(&$date) {
        $date = date('c',strtotime("$date UTC"));
        return $date;
    }

    protected function request($url) {
        \Tracy\Debugger::timer("tapi-request" . spl_object_hash($this));
        $ch = curl_init(); 
        curl_setopt($ch, CURLOPT_URL, $url); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if(isset($this->postParams) && is_array($this->postParams)){
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($this->postParams));
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json')
            );
        }
        $result = curl_exec($ch);
        $output = new \stdClass();
        $output->status = $result === FALSE ? FALSE : TRUE;
        $output->result = $result;
        $output->curlInfo = curl_getinfo ($ch);
        curl_close($ch);
        $this->tymyPanel->logAPI("TAPI request", $this->fullUrl, \Tracy\Debugger::timer("tapi-request" . spl_object_hash($this)));
        return $output;
    }
    
    /**
     * Adds post parameter to prepared request. If Key is an array, whole array is added to the body. Otherwise a standard key/value pair is added.
     * @param array or string $key
     * @param string $value
     * @return $this
     */
    protected function addPost($key, $value = NULL){
        if($value == NULL && is_array($key)){
            $this->postParams[] = $key;
        } else {
            $this->postParams[$key] = $value;
        }
        return $this;
    }

    public function https($https = FALSE){
        $this->protocol = $https ? "https" : "http";
        return $this;
    }
    
    public function recId($recId){
        $this->recId = $recId;
        return $this;
    }
    
    protected function checkPlayerData(&$player) {
        $player->errCnt = 0;
        $player->errFls = [];
        if (!isset($player->firstName) || empty($player->firstName)) {
            $player->errCnt++;
            $player->errFls[] = "firstName";
        }
        if (!isset($player->lastName) || empty($player->lastName)) {
            $player->errCnt++;
            $player->errFls[] = "lastName";
        }
        if (!isset($player->gender) || empty($player->gender)) {
            $player->errCnt++;
            $player->errFls[] = "gender";
        }
        if (!isset($player->phone) || empty($player->phone)) {
            $player->errCnt++;
            $player->errFls[] = "phone";
        }
        if (!isset($player->email) || empty($player->email) || filter_var($player->email, FILTER_VALIDATE_EMAIL) === FALSE) {
            $player->errCnt++;
            $player->errFls[] = "email";
        }
        if (!isset($player->birthDate) || empty($player->birthDate)) {
            $player->errCnt++;
            $player->errFls[] = "birthDate";
        }
        if (!isset($player->callName) || empty($player->callName)) {
            $player->errCnt++;
            $player->errFls[] = "callName";
        }
        if (!isset($player->jerseyNumber) || empty($player->jerseyNumber)) {
            $player->errCnt++;
            $player->errFls[] = "jerseyNumber";
        }
        if (!isset($player->street) || empty($player->street)) {
            $player->errCnt++;
            $player->errFls[] = "street";
        }
        if (!isset($player->city) || empty($player->city)) {
            $player->errCnt++;
            $player->errFls[] = "city";
        }
        if (!isset($player->zipCode) || empty($player->zipCode)) {
            $player->errCnt++;
            $player->errFls[] = "zipCode";
        }
    }
}