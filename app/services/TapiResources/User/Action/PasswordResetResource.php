<?php

namespace Tapi;
use Tymy\Exception\APIException;

/**
 * Project: tymy_v2
 * Description of PasswordResetResource
 *
 * @author kminekmatej created on 29.12.2017, 19:53:04
 */
class PasswordResetResource extends UserResource {
    
    protected function init() {
        $this->setCacheable(FALSE);
        $this->setTsidRequired(FALSE);
    }

    protected function preProcess() {
        if($this->getCode() == null)
            throw new APIException('Code not set!');
        
        $this->setUrl("pwdreset/" . $this->getCode());

        return $this;
    }
    
    protected function postProcess() {
        
    }
    
    public function getCode() {
        return $this->options->code;
    }

    public function setCode($code) {
        $this->options->code = $code;
        return $this;
    }


}
