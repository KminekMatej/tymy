<?php

namespace Tapi;

/**
 * EditableDao is DAO which can find records (by extending CommonDao) and can handle Edit and Create operations
 *
 * @author kminekmatej
 */
abstract class EditableDao extends CommonDao{
    
    abstract function getEditUrl();
    abstract function getCreateUrl();
    
    public function edit(TapiEntity $entity) {
        $this->method = "PUT";
        $this->url = $this->getEditUrl();
    }

    public function create(TapiEntity $entity) {
        $this->method = "POST";
        $this->url = $this->getCreateUrl();
    }
}
