<?php

namespace Tapi;

/**
 * Tapi result status
 *
 * @author kminekmatej
 */
class ResultStatus {
    const OK = "OK";
    const ERROR = "ERROR";
    
    private $valid;
    private $status;
    private $message;
    private $data;
    private $object;
    
    
    public function __construct($tapiResponse) {
        $this->status = $tapiResponse->status;
        $this->message = empty($tapiResponse->statusMessage) ? null : $tapiResponse->statusMessage;
        $this->data = $tapiResponse->data;
        $this->object = $tapiResponse;
        $this->valid = $this->status == self::OK;
    }
    
    
    public function isValid(){
        return $this->valid;
    }
    
    public function getStatus() {
        return $this->status;
    }

    public function getMessage() {
        return $this->message;
    }
    
    public function getData() {
        return $this->data;
    }
    
    public function getObject() {
        return $this->object;
    }

    public function setObject($object) {
        $this->object = $object;
        return $this;
    }

}
