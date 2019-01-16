<?php

namespace Tapi;

/**
 * Description of RightListResource
 *
 * @author kminekmatej, 15.1.2019
 */
class RightListResource extends PermissionResource{
    
    public function init() {
        parent::globalInit();
        $this->setCachingTimeout(TapiObject::CACHE_TIMEOUT_LARGE);
        return $this;
    }

    protected function preProcess() {
        $this->setUrl("rights/user");
    }

    protected function postProcess() {
        $this->options->asArray = [];
        
        if ($this->data == null)
            return null;

        foreach ($this->data as $right) {
            parent::postProcessRight($right);
            $this->options->asArray[$right->type] = $right->name;
        }
    }
    
    public function getAsArray() {
        return $this->options->asArray;
    }

}
