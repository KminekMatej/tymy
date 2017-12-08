<?php

namespace Tapi;

/**
 * Project: tymy_v2
 * @author kminekmatej created on 8.12.2017, 9:48:27
 */
class CachedResult {
    
    private $timeout;
    private $data;
    
    public function __construct($timeout, $data) {
        $this->timeout = $timeout;
        $this->data = $data;
    }
    
    public function isValid(){
        return date("U") <= $this->timeout;
    }
    
    public function getValidity(){
        return max(0, $this->timeout - date("U"));
    }
    
    public function load() {
        return $this->isValid() ? $this->data : null;
    }
    
    public function getData() {
        return $this->data;
    }

    public function setTimeout($timeout) {
        $this->timeout = $timeout;
        return $this;
    }

}
