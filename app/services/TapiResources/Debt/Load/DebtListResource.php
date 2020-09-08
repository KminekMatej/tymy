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
        if(empty($this->data)){
            return;
        }
        $this->options->warnings = 0;
        usort($this->data, function($a, $b) {
            return $a->canSetSentDate ? -1 : 1;
        });
        $debtsFromMe = [];
        $debtsFromTeam = [];
        $debtsToMe = [];
        $debtsToTeam = [];
        foreach ($this->data as $debt) {
            parent::postProcessDebt($debt);
            if ($debt->canSetSentDate && empty($debt->paymentSent)) {
                $this->options->warnings += 1;
            }
            if ($debt->debtorId == $this->user->id) {
                $debtsFromMe[] = $debt;
            } else if ($debt->debtorType == self::TYPE_TEAM) {
                $debtsFromTeam[] = $debt;
            } else if ($debt->payeeType == self::TYPE_TEAM) {
                $debtsToTeam[] = $debt;
            } else {
                $debtsToMe[] = $debt;
            }
        }
        $this->data = array_merge($debtsFromMe, $debtsFromTeam, $debtsToTeam, $debtsToMe);
    }
    
    

}
