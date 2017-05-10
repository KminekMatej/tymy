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
    protected $presenter;
    protected $team;
    /**
     * recId - root id of record (discussion id, event id, ...)
     * $recId integer 
     */
    protected $recId;
    protected $fullUrl;
    private $user;
    private $uriParams;
    private $postParams;
    
    
    /** Function to return full URI of select api */
    abstract protected function select();
    
    /** Function to return fields supposed to be converted from UTC to proper timezone */
    abstract protected function tzFields($jsonObj);
    
    public function __construct(Nette\Application\UI\Presenter $presenter = NULL) {
        if($presenter != NULL)
            $this->presenter ($presenter);
        $this->https(FALSE);
    }
    
    public function presenter(Nette\Application\UI\Presenter $presenter){
        $this->presenter = $presenter;
        $this->user = $presenter->getUser();
        $this->team($presenter->getUser()->getIdentity()->data["tym"]);
        $this->setUriParam("TSID", $presenter->getUser()->getIdentity()->data["sessionKey"]);
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
        $this->fullUrl .= "/" . $this->composeUriParams();
        return $this;
    }

    public function fetch(){
        $this->urlStart();

        $this->select();

        $this->urlEnd();
        //\Tracy\Debugger::barDump($this->fullUrl);
        
        try {
            $this->result = $this->execute();
        } catch (\Tymy\Exception\APIAuthenticationException $exc) {
            $this->user->logout(true);
            $this->presenter->flashMessage('You have been signed out due to inactivity. Please sign in again.');
            $this->presenter->redirect('Sign:in', ['backlink' => $this->presenter->storeRequest()]);
        }
        
        $data = $this->getData();

        $this->tzFields($data);
        
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
    
    protected function execute() {
        $contents = $this->request($this->fullUrl);
        if ($contents->status) {
            if ($contents->curlInfo["http_code"] == 401) { // not logged in
                throw new \Tymy\Exception\APIAuthenticationException("API request ". $this->fullUrl ." retuned error 401 - Not Authorized");
            }
            
            if ($contents->curlInfo["http_code"] != 200) {
                throw new \Tymy\Exception\APIException("API request ". $this->fullUrl ." retuned wrong error code " . $contents->curlInfo["http_code"]);
            }
            $jsonObj = Json::decode($contents->result);
            
            if ($jsonObj->status == "ERROR" && $jsonObj->statusMessage == "Not loggged in") {
                throw new \Tymy\Exception\APIAuthenticationException("API request ". $this->fullUrl ." retuned error 401 - Not Authorized");
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
    
    protected function timezone(&$date) {
        $date = date('c',strtotime("$date UTC"));
        return $date;
    }

    protected function request($url) {
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
        return $output;
    }
    
    protected function addPost($key, $value){
        $this->postParams[$key] = $value;
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