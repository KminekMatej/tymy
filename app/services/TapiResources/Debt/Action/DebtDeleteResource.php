<?php

namespace Tapi;

/**
 * Project: tymy_v2
 * Description of DebtDeleteResource
 *
 * @author kminekmatej created on 18.2.2018, 20:11:34
 */
class DebtDeleteResource extends DebtResource{

    public function init() {
        parent::globalInit();
        $this->setCacheable(FALSE);
        $this->setMethod(RequestMethod::DELETE);
        return $this;
        
    }

    protected function preProcess() {
        if($this->getId() == null)
            throw new APIException('Debt ID is missing', self::BAD_REQUEST);
        
        $this->setUrl("debt/" . $this->getId());
        
    }

    protected function postProcess() {
        $this->clearCache();
    }

}
