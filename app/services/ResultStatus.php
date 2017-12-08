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
    
    
    public function __construct($tapiResponse) {
        $this->status = $tapiResponse->status;
        $this->message = empty($tapiResponse->statusMessage) ? null : $tapiResponse->statusMessage;
        $this->data = empty($tapiResponse->data) ? null : $tapiResponse->data;
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

}
