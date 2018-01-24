<?php

namespace Tapi;

/**
 * Project: tymy_v2
 * Description of NoteResource
 *
 * @author kminekmatej created on 24.1.2018, 14:20:03
 */
abstract class NoteResource extends TapiObject {
    
    protected function postProcessNote($note){
        //todo
    }
    
    protected function clearCache($id = NULL){
        $this->cache->remove($this->getCacheKey("GET:notes"));
    }
}
