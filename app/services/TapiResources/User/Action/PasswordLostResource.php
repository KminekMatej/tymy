<?php

namespace Tapi;
use Tapi\Exception\APIException;

/**
 * Project: tymy_v2
 * Description of PasswordLostResource
 *
 * @author kminekmatej created on 29.12.2017, 18:42:44
 */

class PasswordLostResource extends UserResource {
    
    public function init() {
        parent::globalInit();
        $this->setCacheable(FALSE);
        $this->setMethod(RequestMethod::POST);
        $this->setTsidRequired(FALSE);
        $this->setCallbackUri(NULL);
        $this->setHostname(NULL);
        $this->setMail(NULL);
        return $this;
    }

    protected function preProcess() {
        if (!$this->getMail())
            throw new APIException('E-mail not set', self::BAD_REQUEST);
        if (!$this->getHostname())
            throw new APIException('Hostname not set', self::BAD_REQUEST);
        if (!$this->getCallbackUri())
            throw new APIException('Callback not set', self::BAD_REQUEST);
        
        $this->setUrl("pwdlost");
        
        $data = [
            "email" => $this->getMail(),
            "callbackUri" => $this->getCallbackUri(),
            "hostname" => $this->getHostname(),
        ];
        
        $this->setRequestData((object)$data);
        
        return $this;
    }
    
    protected function postProcess() {
        
    }
    
    public function getMail() {
        return $this->options->mail;
    }

    public function getCallbackUri() {
        return $this->options->callbackUri;
    }

    public function getHostname() {
        return $this->options->hostname;
    }

    public function setMail($mail) {
        $this->options->mail = $mail;
        return $this;
    }

    public function setCallbackUri($callbackUri) {
        $this->options->callbackUri = $callbackUri;
        return $this;
    }

    public function setHostname($hostname) {
        $this->options->hostname = $hostname;
        return $this;
    }

}
