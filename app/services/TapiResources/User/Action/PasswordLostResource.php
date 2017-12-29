<?php

namespace Tapi;
use Tymy\Exception\APIException;

/**
 * Project: tymy_v2
 * Description of PasswordLostResource
 *
 * @author kminekmatej created on 29.12.2017, 18:42:44
 */

class PasswordLostResource extends UserResource {
    
    private $mail;
    private $callbackUri;
    private $hostname;
    
    public function init() {
        $this->setCacheable(FALSE);
        $this->setMethod(RequestMethod::POST);
        $this->setTsidRequired(FALSE);
    }

    protected function preProcess() {
        if (!isset($this->mail))
            throw new APIException('E-mail not set!');
        if (!isset($this->hostname))
            throw new APIException('Hostname not set!');
        if (!isset($this->callbackUri))
            throw new APIException('Callback not set!');
        
        $this->setUrl("pwdlost");
        
        $data = [
            "email" => $this->mail,
            "callbackUri" => $this->callbackUri,
            "hostname" => $this->hostname,
        ];
        
        $this->setRequestData((object)$data);
        
        return $this;
    }
    
    protected function postProcess() {
        
    }
    
    public function getMail() {
        return $this->mail;
    }

    public function getCallbackUri() {
        return $this->callbackUri;
    }

    public function getHostname() {
        return $this->hostname;
    }

    public function setMail($mail) {
        $this->mail = $mail;
        return $this;
    }

    public function setCallbackUri($callbackUri) {
        $this->callbackUri = $callbackUri;
        return $this;
    }

    public function setHostname($hostname) {
        $this->hostname = $hostname;
        return $this;
    }

}
