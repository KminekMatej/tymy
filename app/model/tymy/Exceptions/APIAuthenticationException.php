<?php

namespace Tymy\Exception;

/**
 * Description of APIAuthenticationException
 *
 * @author matej
 */

class APIAuthenticationException extends \APIException
{
    public function __construct($message, $code = 0, Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }

    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }

}