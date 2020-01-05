<?php

namespace Tapi;

/**
 * Project: tymy_v2
 * Description of DebtListResource
 *
 * @author kminekmatej created on 5.1.2020
 */
class DebtListResource extends DebtResource {
    
    public function init() {
        parent::globalInit();
        return $this;
    }
    
    public function preProcess() {
        $this->setUrl("debt");
        return $this;
    }

    protected function postProcess() {
        $this->options->warnings = 0;
        foreach ($this->data as $debt) {
            parent::postProcessDebt($debt);
            if($debt->canSetSentDate && empty($debt->paymentSent)) $this->options->warnings += 1;
        }
        
    }


}
