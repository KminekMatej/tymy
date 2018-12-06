<?php

namespace Tapi;

use Tapi\Exception\APIException;

/**
 * Project: tymy_v2
 * Description of LoginTkResource
 *
 * @author kminekmatej created on 6.12.2018, 14:47:00
 */
class LoginTkResource extends UserResource {

    private $tk;

    public function init() {
        parent::globalInit();
        $this->setCacheable(FALSE);
        $this->setTsidRequired(FALSE);
        $this->setTk(NULL);
        return $this;
    }

    protected function preProcess() {
        $this->sessionKey = NULL;
        if ($this->getTk() == null)
            throw new APIException('Transfer key is missing', self::BAD_REQUEST);

        $this->setUrl("loginTk?tk=" . $this->getTk());

        return $this;
    }

    protected function postProcess() {
        $this->data->sessionKey = $this->sessionKey;
        \Tracy\Debugger::barDump($this->data);
        parent::postProcessUser($this->data);
    }

    public function getTk() {
        return $this->options->tk;
    }

    public function setTk($tk) {
        $this->options->tk = $tk;
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
