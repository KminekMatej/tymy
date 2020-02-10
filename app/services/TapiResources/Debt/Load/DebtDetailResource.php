<?php

namespace Tapi;
use Tapi\Exception\APIException;

/**
 * Project: tymy_v2
 * Description of DebtDetailResource
 *
 * @author kminekmatej created on 10.02.2020
 */
class DebtDetailResource extends DebtResource {
    
    public function init() {
        parent::globalInit();
        //everything inited correctly
        return $this;
    }

    protected function preProcess() {
        if($this->getId() == null)
            throw new APIException('Debt ID is missing', self::BAD_REQUEST);
        $this->setUrl("debt/" . $this->getId());
    }
    
    protected function postProcess() {
        parent::postProcessDebt($this->data);
    }

}
