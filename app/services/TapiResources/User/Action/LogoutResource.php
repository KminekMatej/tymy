<?php

namespace Tapi;

/**
 * Project: tymy_v2
 * Description of LogoutResource
 *
 * @author kminekmatej created on 29.12.2017, 19:56:24
 */
class LogoutResource extends UserResource {
    
    public function init() {
        $this->setCacheable(FALSE);
        return $this;
    }

    protected function preProcess() {
        $this->setUrl("logout");
        return $this;
    }
    
    protected function postProcess() {
        $this->cleanCache();
    }
    
}
