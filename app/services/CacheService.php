<?php

namespace Tapi;
use Tracy\Debugger;

/**
 * Project: tymy_v2
 * Description of CacheService
 *
 * @author kminekmatej created on 27.12.2017, 18:35:19
 */
class CacheService {
    
    const TAPI_SECTION = "TAPI_SECTION";
    const TIMEOUT_NONE = 0; // turn off caching
    const TIMEOUT_TINY = 60; // turn off caching
    const TIMEOUT_SMALL = 180; // 3 minutes - smallest allowed timeout
    const TIMEOUT_MEDIUM = 300; // 5 minutes - medium timeout
    const TIMEOUT_LARGE = 600; // 10 minutes - timeout larger than user usually stays on site
    
    /** @var \Nette\Http\SessionSection */
    private $tapiSection;
    
    public function __construct(\Nette\Http\Session $session) {
        $this->tapiSection = $session->getSection(self::TAPI_SECTION);
    }

    
    public function save($key, $timeout, $value, $options = NULL) {
        if(is_null($this->tapiSection)) return null;
        $this->tapiSection[$key] = new CachedResult(date("U") + $timeout, $value, $options);
    }
    
    /**
     * 
     * @param string $key
     * @return CachedResult
     */
    public function load($key){
        if(is_null($this->tapiSection)) return null;
        $cachedResult = $this->tapiSection[$key];
        return $cachedResult == null || !$cachedResult->isValid() ? null : $cachedResult;
    }
    
    public function clear($key){
        if(is_null($this->tapiSection)) return null;
        unset($this->tapiSection[$key]);
    }
    
    public function dumpCache($return = FALSE, $dump_key = NULL){
        $cacheContents = [];
        foreach ($this->tapiSection as $key => $val) {
            if($dump_key == $key || $key == NULL)
                $cacheContents[$key] = $val;
        }
        if($return) return $cacheContents;
        else Debugger::barDump($cacheContents, "Contents of cache " . self::TAPI_SECTION);
    }
    
    public function dropCache(){
        foreach ($this->tapiSection as $key => $val) {
            unset($this->tapiSection[$key]);
        }
    }
}
