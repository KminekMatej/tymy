<?php

namespace Tapi;
use Tapi\Exception\APIException;

/**
 * Project: tymy_v2
 * Description of LoginResource
 *
 * @author kminekmatej created on 29.12.2017, 19:56:00
 */
class LoginResource extends UserResource  {
    
    protected function init() {
        $this->setCacheable(FALSE);
        $this->setTsidRequired(FALSE);
    }

    protected function preProcess() {
        $this->sessionKey = NULL;
        if($this->options->login == null)
            throw new APIException('Login not set!');
        
        $this->setUrl("login/" . $this->options->login . "/" . $this->options->hash);

        return $this;
    }
    
    protected function postProcess() {
        $this->data->sessionKey = $this->sessionKey;
    }
    
    public function setLogin($login) {
        $this->options->login = $login;
        return $this;
    }

    public function setPassword($password) {
        $h = "";
        $n = rand(1, 19); // password given is already hashed by md5 - therefore max should be 19 to have at most 20 md5 hashings. Min is 1 to have at least one hash
        for ($index = 0; $index < $n; $index++) {
            $h = md5($password);
        }
        $this->options->hash = $h;
        return $this;
    }
    
    public function getData($forceRequest = FALSE) {
        $this->preProcess();
        $this->dataReady = FALSE;
        $resultStatus = $this->requestFromApi(FALSE);
        $this->data->sessionKey = $resultStatus->getObject()->sessionKey;
        return $this->data;
    }


}
