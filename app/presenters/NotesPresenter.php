<?php

namespace Tymy\Module\Core\Presenter\Front;
use Tapi\Exception\APIException;


/**
 * Project: tymy_v2
 * Description of NotesPresenter
 *
 * @author kminekmatej created on 26.1.2018, 21:09:37
 */
class NotesPresenter extends SecuredPresenter {
    
    public $navbar;
    
    protected function startup() {
        parent::startup();
        $this->setLevelCaptions(["1" => ["caption" => $this->translator->translate("note.note", 2), "link" => $this->link("Notes:")]]);
    }

    
    public function renderDefault() {
        //parent::showNotes();
        try {
            $this->template->notes = $this->noteList->init()->setMenu(TRUE)->getData();
        } catch (APIException $ex) {
            $this->handleTapiException($ex);
        }
    }
    
    public function renderNote($poznamka){
        $id = $this->parseIdFromWebname($poznamka);
        //parent::showNotes($id);
        try {
            $this->noteList->init()->getData();
            $note = $this->noteList->getById()[$id];
            $this->template->note = $note;
        } catch (APIException $ex) {
            $this->handleTapiException($ex);
        }
        $this->setLevelCaptions(["2" => ["caption" => $note->caption, "link" => $this->link("Notes:note", $note->id . "-" . $note->webName)]]);
    }
    
}
