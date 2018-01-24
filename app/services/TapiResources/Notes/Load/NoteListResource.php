<?php

namespace Tapi;

/**
 * Project: tymy_v2
 * Description of NoteListResource
 *
 * @author kminekmatej created on 24.1.2018, 14:16:26
 */
class NoteListResource extends NoteResource {
    
    public function init() {
        parent::globalInit();
        $this->setCachingTimeout(TapiObject::CACHE_TIMEOUT_LARGE);
        return $this;
    }
    
    protected function preProcess() {
        $this->setUrl("notes/html");
    }

    protected function postProcess() {
        foreach ($this->data as $note) {
            parent::postProcessNote($note);
            $this->options->bySpecialPage[$note->specialPage] = $note;
            $this->options->byId[$note->id] = $note;
            
        }
    }
    
    public function getBySpecialPage($page){
        return $this->options->bySpecialPage[$page];
    }

    public function getById($id){
        return $this->options->byId[$id];
    }

}
