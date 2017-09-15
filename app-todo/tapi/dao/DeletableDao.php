<?php

namespace Tapi;

/**
 * DeletableDao is DAO which can find records (by extending CommonDao), edit and create records (by extending EditableDap) and can handle Delete operation
 *
 * @author kminekmatej
 */
abstract class DeletableDao extends EditableDao{
    
    abstract function getDeleteUrl();
    
    public function delete(TapiEntity $entity) {
        $this->method = "DELETE";
        $this->url = $this->getDeleteUrl();
    }

}
