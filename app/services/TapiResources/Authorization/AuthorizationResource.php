<?php

namespace Tapi;
use Nette\Utils\Strings;
use Nette\Caching\Cache;

/**
 * Project: tymy_v2
 * Description of UserResource
 *
 * @author kminekmatej created on 22.12.2017, 21:04:36
 */
abstract class AuthorizationResource extends TapiObject{
    
    protected function postProcessAuthorization($auth){
        //TODO work with obtained data
    }
    
    protected function clearCache($id){
        $this->cache->clean([Cache::TAGS => "GET:authorization/$id"]);
    }
    
}
