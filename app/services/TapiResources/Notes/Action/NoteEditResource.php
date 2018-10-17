<?php

namespace Tapi;

/**
 * Project: tymy_v2
 * Description of NoteEditResource
 *
 * @author kminekmatej created on 18.2.2018, 9:18:43
 */
class NoteEditResource extends NoteResource {
    
    public function init() {
        parent::globalInit();
        $this->setCacheable(FALSE);
        $this->setMethod(RequestMethod::PUT);
        $this->options->note = NULL;
        return $this;
    }
    
    protected function preProcess() {
        if ($this->getId() == null)
            throw new APIException('Note ID is missing', self::BAD_REQUEST);
        if ($this->getNote() == null)
            throw new APIException('Note object is missing', self::BAD_REQUEST);

        $this->setUrl("notes");
        $note = $this->getNote();
        $note["id"] = $this->getId();
        if(array_key_exists("menuType", $note))
            $note["menuType"] = $note["menuType"] ? "APP" : "NO";
        $this->setNote($note);
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
