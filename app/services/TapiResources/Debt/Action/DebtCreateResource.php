<?php

namespace Tapi;

/**
 * Project: tymy_v2
 * Description of DebtCreateResource
 *
 * @author kminekmatej created on 25.6.2020
 */
class DebtCreateResource extends DebtResource {

    public function init() {
        parent::globalInit();
        $this->setCacheable(FALSE);
        $this->setMethod(RequestMethod::POST);
        $this->options->debt = NULL;
        return $this;
    }
    
    protected function preProcess() {
        if ($this->getDebt() == null)
            throw new APIException('Debt object is missing', self::BAD_REQUEST);
        $debt = $this->getDebt();
        $this->setUrl("debt");
        $this->setRequestData($debt);
        return $this;
    }

    protected function postProcess() {
        $this->clearCache();
    }
    
    public function getDebt() {
        return $this->options->debt;
    }

    public function setDebt($debt) {
        $this->options->debt = $debt;
        return $this;
    }


}
