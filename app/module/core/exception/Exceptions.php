<?php

namespace Tymy\Module\Core\Exception;

use Exception;
use Nette\Http\Response;
use Throwable;

/**
 * Description of DebugResponse
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 8.1.2022
 */
class DebugResponse extends Exception
{
}

class MissingInputException extends Exception
{
}

class TymyResponse extends Exception
{
    private ?int $httpCode = null;
    private bool $success = true;
    private ?string $sessionKey = null;
    private ?string $redirect = null;

    /** @var mixed */
    private $payload = null;

    public function __construct(string $message = "", int $httpCode = Response::S200_OK, ?int $code = null, $payload = null, bool $success = true, ?string $sessionKey, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->success = $success;
        $this->sessionKey = $sessionKey;
        $this->code = $code;
        $this->payload = $payload;
        $this->httpCode = $httpCode;
    }

    public function getHttpCode(): ?int
    {
        return $this->httpCode;
    }

    public function getSuccess(): bool
    {
        return $this->success;
    }

    public function getSessionKey(): ?string
    {
        return $this->sessionKey;
    }

    public function getPayload()
    {
        return $this->payload;
    }

    public function getRedirect(): ?string
    {
        return $this->redirect;
    }

    public function setHttpCode(?int $httpCode)
    {
        $this->httpCode = $httpCode;
        return $this;
    }

    public function setSuccess(bool $success)
    {
        $this->success = $success;
        return $this;
    }

    public function setSessionKey(?string $sessionKey)
    {
        $this->sessionKey = $sessionKey;
        return $this;
    }

    public function setPayload($payload)
    {
        $this->payload = $payload;
        return $this;
    }

    public function setRedirect(?string $redirect)
    {
        $this->redirect = $redirect;
        return $this;
    }
}
