<?php

namespace Tapi;

/**
 * Project: tymy_v2
 * Description of LogoutResource
 *
 * @author kminekmatej created on 29.12.2017, 19:56:24
 */
class LogoutResource extends UserResource {
    
    protected function init() {
        $this->setCacheable(FALSE);
    }

    protected function preProcess() {
        $this->setUrl("logout");
        return $this;
    }
    
    protected function postProcess() {
        $this->cacheService->dropCache();
    }
    
}
