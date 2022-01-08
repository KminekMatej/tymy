<?php
namespace Tymy\Module\Core\Exception;

use Exception;

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

    private bool $success = true;
    private ?string $sessionKey = null;
    private mixed $payload = null;
    
    public function __construct(string $message = "", int $code = 0, mixed $payload, bool $success = true, ?string $sessionKey, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->success = $success;
        $this->sessionKey = $sessionKey;
        $this->payload = $payload;
    }

        public function getSuccess(): bool
    {
        return $this->success;
    }

    public function getSessionKey(): ?string
    {
        return $this->sessionKey;
    }

    public function getPayload(): mixed
    {
        return $this->payload;
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

    public function setPayload(mixed $payload)
    {
        $this->payload = $payload;
        return $this;
    }
}
