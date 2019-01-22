<?php

namespace Tapi;

/**
 * Description of EventHistoryResrouce
 *
 * @author kminekmatej, 22.1.2019
 */
class EventHistoryResrouce extends EventResource {

    public function init() {
        parent::globalInit();
        return $this;
    }

    protected function preProcess() {
        if($this->getId() == null)
            throw new APIException('Event ID is missing', self::BAD_REQUEST);
        $this->setUrl("event/" . $this->getId() . "/history");
    }

    protected function postProcess() {
        if($this->data == null)
            return null;
        
        foreach ($this->data as $history) {
            parent::postProccessEventHistory($history);
        }
    }

}
