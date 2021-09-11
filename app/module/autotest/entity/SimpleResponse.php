<?php

namespace Tymy\Test;

use Nette\Application\IResponse;
use Nette\Application\Request;
use Nette\Application\UI\Presenter;
use Nette\Http\Response;
use Tymy\Test\Entity\Assert;

/**
 * Class for easy checking response
 */
class SimpleResponse
{
    /** @var int */
    private $code;

    /** @var string */
    private $data;

    private ?string $message = null;

    /** @var Request */
    private $httpRequest;

    /** @var Response */
    private $httpResponse;

    /** @var IResponse */
    private $response;

    /** @var Presenter */
    private $presenter;

    /** @var RequestLog */
    private $log;

    public function __construct($code, $data, Request $httpRequest, Response $httpResponse, IResponse $response, Presenter $presenter, RequestLog &$log)
    {
        $this->code = $code;
        $this->data = $data;
        $this->httpRequest = $httpRequest;
        $this->httpResponse = $httpResponse;
        $this->response = $response;
        $this->presenter = $presenter;
        $this->log = $log;
        $this->log->setHttpResponseCode($httpResponse->getCode());
        if ($data && is_array($data) && array_key_exists("code", $data)) {
            $this->log->setCustomResponseCode((int) $data["code"]);
        }
        if (is_array($response->getPayload()) && array_key_exists("statusMessage", $response->getPayload())) {
            $this->message = $response->getPayload()["statusMessage"];
        }
    }

    public function getCode()
    {
        return $this->code;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getHttpRequest()
    {
        return $this->httpRequest;
    }

    public function getHttpResponse()
    {
        return $this->httpResponse;
    }

    public function getResponse()
    {
        return $this->response;
    }

    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    public function setHttpRequest(Request $httpRequest)
    {
        $this->httpRequest = $httpRequest;
        return $this;
    }

    public function setHttpResponse(Response $httpResponse)
    {
        $this->httpResponse = $httpResponse;
        return $this;
    }

    public function setResponse(IResponse $jsonResponse)
    {
        $this->response = $jsonResponse;
        return $this;
    }

    public function getPresenter()
    {
        return $this->presenter;
    }

    public function setPresenter(Presenter $presenter)
    {
        $this->presenter = $presenter;
        return $this;
    }

    public function expect(int $code, ?string $type = null)
    {
        $this->log->setExpectCode($code);
        if ($code < 999) {
            $message = $this->message ? "Message: {$this->message}. " : "";
            Assert::equal($code, $this->getCode(), "Expected HTTP response code mismatch. {$message}Output: " . print_r($this->getData(), true));
        } else {    //over 1000 are app-specific error codes
            Assert::errcode($code, $this);
        }

        if ($type) {
            Assert::type($type, $this->getData(), "Expected data type mismatch. Output: " . print_r($this->getData(), true));
        }
        return $this;
    }
}
