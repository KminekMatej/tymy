<?php

namespace Tapi;
use Nette\Caching\Cache;

/**
 * Project: tymy_v2
 * Description of NoteResource
 *
 * @author kminekmatej created on 24.1.2018, 14:20:03
 */
abstract class NoteResource extends TapiObject {
    
    protected function postProcessNote($note){
        if($note == null) TapiService::throwNotFound();
        $note->warnings = 0;
        if(!property_exists($note, "description"))
                $note->description = "";
        if(!property_exists($note, "caption"))
                $note->caption = "";
        if(!property_exists($note, "specialPage"))
                $note->specialPage = "";
        if(!property_exists($note, "source"))
                $note->source = "";
        
        $note->webName = \Nette\Utils\Strings::webalize($note->id . "-" . $note->caption);
        $note->menuType = $note->menuType == "APP" ? true : false;
        $note->shown = FALSE;
    }
    
    protected function clearCache(){
        $this->cache->clean([Cache::TAGS => $this->supplier->getTym() . "@GET:notes"]);
        $this->cache->clean([Cache::TAGS => $this->supplier->getTym() . "@GET:notes/html"]);
    }
}
