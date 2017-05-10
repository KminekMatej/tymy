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
    
    protected $result = NULL;
    
    protected $protocol;
    protected $presenter;
    protected $teamUri;
    protected $root;
    /**
     * recId - root id of record (discussion id, event id, ...)
     * $recId integer 
     */
    protected $recId;
    private $condition;
    private $limit;
    private $offset;
    protected $fullUrl;
    private $user;
    private $uriParams;
    private $postParams;
    
    
    /** Function to return full URI of select api */
    abstract protected function select();
    
    /** Function to return fields supposed to be converted from UTC to proper timezone */
    abstract protected function tzFields($jsonObj);
    
    public function __construct(Nette\Application\UI\Presenter $presenter) {
        $this->presenter = $presenter;
        $this->user = $presenter->getUser();
        $this->root($this->user->getIdentity()->tym);
        $this->teamUri = $this->user->getIdentity()->tym . ".tymy.cz/api/";
        $this->https(FALSE);
        $this->setUriParam("TSID", $this->user->getIdentity()->tsid);
    }

    protected function setUriParam($key, $value) {
        $this->uriParams[$key] = $value;
    }
    
    protected function composeUriParams(){
        return "?" . http_build_query($this->uriParams);
    }
    
    protected function urlStart() {
        $this->fullUrl = $this->protocol;
        if (!isset($this->teamUri))
            throw new TymyException('Uri not set!');

        $this->fullUrl .= "://" . $this->teamUri;
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
        
        $result = $this->execute();
        
        $this->tzFields($this->result);
        
        return $result;
    }
    
    protected function execute() {
        $contents = $this->request($this->fullUrl);
        if ($contents->status) {
            if ($contents->curlInfo["http_code"] == 401) { // not logged in
                $this->user->logout(true);
                $this->presenter->flashMessage('You have been signed out due to inactivity. Please sign in again.');
                $this->presenter->redirect('Sign:in', ['backlink' => $this->presenter->storeRequest()]);
            }
            
            if ($contents->curlInfo["http_code"] != 200) {
                throw new TymyException("Dotaz na server vrátil chybný návratový kód (" . $contents->curlInfo["http_code"] . ")");
            }
            $jsonObj = Json::decode($contents->result);
            
            if ($jsonObj->status == "ERROR" && $jsonObj->statusMessage == "Not loggged in") {
                $this->user->logout(true);
                $this->presenter->flashMessage('You have been signed out due to inactivity. Please sign in again.');
                $this->presenter->redirect('Sign:in', ['backlink' => $this->presenter->storeRequest()]);
            }
            
            if ($jsonObj->status != "OK") {
                throw new TymyException("Server API vrátilo chybný návratový kód " . $jsonObj->status . " : " . $jsonObj->statusMessage);
            }
            
            $this->result = (object) $jsonObj->data;

            return $this->result;
        } else {
            throw new TymyException("Nastala neošetřená výjimka ve funkci Tymy->execute(). Prosím kontaktujte vývojáře.");
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

    public function root($root){
        $this->root = $root;
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
    
    /**
     * Sets limit clause, more calls rewrite old values.
     * @param  int
     * @param  int
     * @return static
     */
    public function limit($limit, $offset = NULL) {
        $this->limit = $limit;
        $this->offset = $offset;
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


class TymyException extends \Exception
{
    public function __construct($message, $code = 0, Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }

    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }

}