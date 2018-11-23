<?php

namespace Tapi;
use Nette\Caching\Cache;

/**
 * Project: tymy_v2
 * Description of MultiaccountResource
 *
 * @author kminekmatej created on 22.11.2018, 22:34:03
 */
abstract class MultiaccountResource extends TapiObject {
    
    protected function postProcessSimpleTeam($steam){
        if($steam == null) TapiService::throwNotFound();
        $steam->warnings = 0;
    }
    
    protected function clearCache(){
        $this->cache->clean([Cache::TAGS => $this->supplier->getTym() . "@GET:multiaccount"]);
    }
}
