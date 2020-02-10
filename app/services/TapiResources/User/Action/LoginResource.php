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
    
    public function init() {
        parent::globalInit();
        $this->setCacheable(FALSE);
        $this->setTsidRequired(FALSE);
        $this->setLogin(NULL);
        $this->setMethod(RequestMethod::POST);
        $this->setEncoding(self::ENCODING_URLENCODED);
        return $this;
    }

    protected function preProcess() {
        $this->sessionKey = NULL;
        if($this->options->login == null)
            throw new APIException('Login is missing', self::BAD_REQUEST);
        
        $this->setUrl("login");
        
        $this->setRequestData([
            "login" => $this->options->login,
            "password" => $this->options->hash,
            "requests" => "news",
        ]);
        
        return $this;
    }
    
    protected function postProcess() {
        \Tracy\Debugger::barDump($this->data, "Response data");
        $this->data->sessionKey = $this->sessionKey;
        parent::postProcessUser($this->data->user);
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
