<?php

namespace App\Presenters;
use Tapi\Exception\APIException;


/**
 * Project: tymy_v2
 * Description of NotesPresenter
 *
 * @author kminekmatej created on 26.1.2018, 21:09:37
 */
class NotesPresenter extends SecuredPresenter {
    
    public $navbar;
    
    public function renderDefault() {
        try {
            $this->template->notes = $this->noteList->init()->getData();
        } catch (APIException $ex) {
            $this->handleTapiException($ex);
        }
    }
    
    public function renderNote($note){
        $id = $this->parseIdFromWebname($note);
        try {
            $this->noteList->init()->getData();
            $this->template->note = $this->noteList->getById()[$id];
        } catch (APIException $ex) {
            $this->handleTapiException($ex);
        }
    }
    
}
