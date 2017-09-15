<?php

namespace Tapi;

/**
 * Description of TapiEntitys
 *
 * @author kminekmatej
 */
class TapiEntity {
    private $id;
    
    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
        return $this;
    }

}
