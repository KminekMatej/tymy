<?php

namespace Tymy;

use Nette;
use Nette\Utils\Strings;

/**
 * Description of Tymy
 *
 * @author matej
 */
final class EventTypes extends Tymy{
    
    const TAPI_NAME = "eventTypes";
    const TSID_REQUIRED = TRUE;
    
    public function select() {
        $this->fullUrl .= self::TAPI_NAME;
    }
    
    protected function postProcess(){
        if (($data = $this->getData()) == null)
            return;
        $newData = [];
        foreach ($data as $value) {
            $newPreStatus = [];
            foreach ($value->preStatusSet as $status) {
                $newPreStatus[$status->code] = $status;
            }
            $value->preStatusSet = $newPreStatus;
            $newPostStatus = [];
            foreach ($value->postStatusSet as $status) {
                $newPostStatus[$status->code] = $status;
            }
            $value->postStatusSet = $newPostStatus;
            $newData[$value->code] = $value;
        }
        $this->result->data = $newData;
    }

}
