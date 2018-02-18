<?php

namespace Tapi;

/**
 * Project: tymy_v2
 * Description of NoteDeleteResource
 *
 * @author kminekmatej created on 18.2.2018, 20:11:34
 */
class NoteDeleteResource extends NoteResource{

    public function init() {
        parent::globalInit();
        $this->setCacheable(FALSE);
        $this->setMethod(RequestMethod::DELETE);
        return $this;
        
    }

    protected function preProcess() {
        if($this->getId() == null)
            throw new APIException('Note ID not set');
        
        $this->setUrl("notes/" . $this->getId());
        
    }

    protected function postProcess() {
        $this->clearCache();
    }

}
