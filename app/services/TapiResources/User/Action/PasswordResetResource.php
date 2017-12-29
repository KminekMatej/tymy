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
    
    private $code;
    
    public function init() {
        $this->setCacheable(FALSE);
        $this->setTsidRequired(FALSE);
    }

    protected function preProcess() {
        if($this->code == null)
            throw new APIException('Code not set!');
        
        $this->setUrl("pwdreset/" . $this->code);

        return $this;
    }
    
    protected function postProcess() {
        
    }
    
    public function getCode() {
        return $this->code;
    }

    public function setCode($code) {
        $this->code = $code;
        return $this;
    }


}
