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
        $this->options->bySpecialPage = NULL;
        $this->options->byId = NULL;
        return $this;
    }
    
    protected function preProcess() {
        $this->setUrl("notes/html");
        $this->options->bySpecialPage = [];
        $this->options->byId = [];
    }

    protected function postProcess() {
        foreach ($this->data as $note) {
            parent::postProcessNote($note);
            $this->options->bySpecialPage[$note->specialPage] = $note;
            $this->options->byId[$note->id] = $note;
            
        }
    }
    
    public function getBySpecialPage(){
        return $this->options->bySpecialPage;
    }
    
    public function getById(){
        return $this->options->byId;
    }


}
