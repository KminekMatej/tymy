<?php

namespace Tymy;

use Nette;
use Nette\Utils\Json;

/**
 * Description of Tymy
 *
 * @author matej
 */
final class PwdLost extends Tymy{
    
    const TAPI_NAME = "pwdlost";
    const TSID_REQUIRED = FALSE;
    
    private $mail;
    private $callbackUri;
    private $hostname;
    
    public function select() {
        if (!isset($this->mail))
            throw new \Tymy\Exception\APIException('E-mail not set!');
        if (!isset($this->hostname))
            throw new \Tymy\Exception\APIException('Hostname not set!');
        if (!isset($this->callbackUri))
            throw new \Tymy\Exception\APIException('Callback not set!');
        
        $this->fullUrl .= self::TAPI_NAME;
        $this->method = "POST";
        
        $data = new \stdClass();
        $data->email = $this->mail;
        $data->callbackUri = $this->callbackUri;
        $data->hostname = $this->hostname;
        $this->setPostData($data);
        
        return $this;
    }
        
    protected function postProcess() {
        if (($data = $this->getData()) == null)
            return;
    }
    
    public function getMail() {
        return $this->mail;
    }

    public function setMail($mail) {
        $this->mail = $mail;
        return $this;
    }

    public function getCallbackUri() {
        return $this->callbackUri;
    }

    public function getHostname() {
        return $this->hostname;
    }

    public function setCallbackUri($callbackUri) {
        $this->callbackUri = urldecode($callbackUri);
        return $this;
    }

    public function setHostname($hostname) {
        $this->hostname = $hostname;
        return $this;
    }

            
    public function reset() {
        $this->mail = NULL;
        $this->hostname = NULL;
        $this->callbackUri = NULL;
        return parent::reset();
    }

}
