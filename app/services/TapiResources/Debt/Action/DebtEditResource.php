<?php

namespace Tapi;

/**
 * Project: tymy_v2
 * Description of DebtEditResource
 *
 * @author kminekmatej created on 18.2.2018, 9:18:43
 */
class DebtEditResource extends DebtResource {
    
    public function init() {
        parent::globalInit();
        $this->setCacheable(FALSE);
        $this->setMethod(RequestMethod::PUT);
        $this->options->debt = NULL;
        return $this;
    }
    
    protected function preProcess() {
        if ($this->getId() == null)
            throw new APIException('Debt ID is missing', self::BAD_REQUEST);
        if ($this->getDebt() == null)
            throw new APIException('Debt object is missing', self::BAD_REQUEST);

        $this->setUrl("debt/{$this->getId()}");
        $debt = $this->getDebt();
        $debt["id"] = $this->getId();
        $this->setDebt($debt);
        $this->setRequestData($debt);
        return $this;
    }

    protected function postProcess() {
        $this->clearCache($this->getId());
    }
    
    public function getDebt() {
        return $this->options->debt;
    }

    public function setDebt($debt) {
        $this->options->debt = $debt;
        return $this;
    }
}
