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
        $this->options->menu = NULL;
        $this->options->bySpecialPage = NULL;
        $this->options->byId = NULL;
        return $this;
    }
    
    protected function preProcess() {
        $this->setUrl($this->getMenu() ? "notes/menu" : "notes/html");
        $this->options->bySpecialPage = [];
        $this->options->byId = [];
    }

    protected function postProcess() {
        if($this->data == null)
            return null;
        
        foreach ($this->data as $note) {
            parent::postProcessNote($note);
            $this->options->bySpecialPage[$note->specialPage] = $note;
            $this->options->byId[$note->id] = $note;
            
        }
    }
    
    public function getBySpecialPage(){
        return $this->options->bySpecialPage;
    }
    
    public function getById($id = NULL){
        if(is_null($id)){
            return $this->options->byId;
        } else {
            return array_key_exists($id, $this->options->byId) ? $this->options->byId[$id] : NULL;
        }
    }
    
    public function getMenu() {
        return $this->options->menu;
    }

    public function setMenu($menu) {
        $this->options->menu = $menu;
        return $this;
    }



}
