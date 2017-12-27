<?php

namespace Tapi;

/**
 * Project: tymy_v2
 * Description of CacheService
 *
 * @author kminekmatej created on 27.12.2017, 18:35:19
 */
class CacheService {
    
    const TAPI_SECTION = "TAPI_SECTION";
    
    /** @var \Nette\Http\SessionSection */
    private $tapiSection;
    
    public function __construct(\Nette\Http\Session $session) {
        $this->tapiSection = $session->getSection(self::TAPI_SECTION);
    }

    
    public function save($key, $value, $timeout) {
        if(is_null($this->session)) return null;
        $this->tapiSection[$key] = new CachedResult(date("U") + $timeout, $value);
    }
    
    public function load($key){
        $cachedResult = $this->tapiSection[$key];
        return $cachedResult == null || !$cachedResult->isValid() ? null : $cachedResult->load();
    }
    
    public function clear($key){
        if(is_null($this->session)) return null;
        unset($this->tapiSection[$key]);
    }
}
