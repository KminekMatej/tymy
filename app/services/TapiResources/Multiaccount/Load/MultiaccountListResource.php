<?php

namespace Tapi;

/**
 * Description of MultiaccountListResource
 *
 * @author kminekmatej, 22.11.2018
 */
class MultiaccountListResource extends MultiaccountResource {

    public function init() {
        parent::globalInit();
        $this->setCachingTimeout(TapiObject::CACHE_TIMEOUT_LARGE);
        return $this;
    }

    protected function preProcess() {
        $this->setUrl("multiaccount");
    }

    protected function postProcess() {
        if($this->data == null)
            return null;
        
        foreach ($this->data as $sTeam) {
            parent::postProcessSimpleTeam($sTeam);
        }
    }

}
