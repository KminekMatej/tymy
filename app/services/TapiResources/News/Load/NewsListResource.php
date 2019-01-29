<?php

namespace Tapi;

/**
 * Description of NewsListResource
 *
 * @author kminekmatej, 29.1.2019
 */
class NewsListResource extends NewsResource {

    public function init() {
        parent::globalInit();
        $this->setCachingTimeout(TapiObject::CACHE_TIMEOUT_DAY);
        return $this;
    }

    protected function preProcess() {
        $this->setUrl("news");
    }

    protected function postProcess() {
        if($this->data == null)
            return null;
        
        foreach ($this->data as $notice) {
            parent::postProcessNotice($notice);
        }
    }

}
