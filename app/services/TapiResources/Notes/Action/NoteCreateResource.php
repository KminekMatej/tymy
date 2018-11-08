<?php

namespace Tapi;

/**
 * Project: tymy_v2
 * Description of NoteCreateResource
 *
 * @author kminekmatej created on 18.2.2018, 9:18:43
 */
class NoteCreateResource extends NoteResource {

    public function init() {
        parent::globalInit();
        $this->setCacheable(FALSE);
        $this->setMethod(RequestMethod::POST);
        $this->options->note = NULL;
        return $this;
    }
    
    protected function preProcess() {
        if ($this->getNote() == null)
            throw new APIException('Note object is missing', self::BAD_REQUEST);
        $note = $this->getNote();
        if(array_key_exists("menuType", $note))
            $note["menuType"] = $note["menuType"] ? "APP" : "NO";
        if(!$this->user->isAllowed("notes", "manageSharedNotes")){
            unset($note["specialPage"]);
            $note["accessType"] = "PRIVATE";
        }
        if(array_key_exists("accessType", $note) && $note["accessType"] == "PRIVATE" && array_key_exists("specialPage", $note))
            unset($note["specialPage"]); // private note cannot have specialPage filled, otherwise returns BRE
        
        $this->setUrl("notes");
        $this->setRequestData($note);
        return $this;
    }

    protected function postProcess() {
        $this->clearCache();
    }
    
    public function getNote() {
        return $this->options->note;
    }

    public function setNote($note) {
        $this->options->note = $note;
        return $this;
    }


}
