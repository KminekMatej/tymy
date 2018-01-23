<?php

namespace Tapi;

/**
 * Project: tymy_v2
 * Description of OptionDeleteResource
 *
 * @author kminekmatej created on 5.1.2018, 15:24:17
 */
class OptionDeleteResource extends PollResource{
    
    public function init() {
        parent::globalInit();
        $this->setCacheable(FALSE);
        $this->setMethod(RequestMethod::DELETE);
        $this->setOptionId(NULL);
        return $this;
    }

    protected function preProcess() {
        if($this->getId() == null)
            throw new APIException('Poll ID not set');
        if($this->getOptionId() == null)
            throw new APIException('Option ID not set');
        
        $this->setUrl("polls/" . $this->getId() . "/options");
        $this->setRequestData(["id" => $this->getOptionId()]);
    }
    
    protected function postProcess() {
        $this->clearCache($this->getId());
    }
    
    public function getOptionId() {
        return $this->options->optionId;
    }

    public function setOptionId($optionId) {
        $this->options->optionId = $optionId;
        return $this;
    }


}
