<?php

namespace Tapi;
use \Nette\Utils\Strings;
use Nette\Caching\Cache;

/**
 * Description of IsResource
 *
 * @author kminekmatej, 19.10.2018
 */
class IsResource extends TapiObject{
    
    public function init() {
        parent::globalInit();
        $this->setCachingTimeout(self::CACHE_TIMEOUT_DAY);
        $this->setTsidRequired(FALSE);
        return $this;
    }
    
    protected function postProcess() {
        $this->data->teamName = property_exists($this->data, "name") && $this->data->name != "" ? $this->data->name : $this->data->sysName;
    }

    protected function preProcess() {
        $this->setUrl("is");
    }

    

}
