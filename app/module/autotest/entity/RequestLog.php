<?php

namespace Tymy\Module\Autotest;

use Nette\Utils\DateTime;

/**
 * Description of RequestLog
 *
 * @author Matej Kminek <matej.kminek@attendees.eu>, 7. 10. 2020
 */
class RequestLog
{
    private DateTime $time;
    private string $method;
    private string $url;
    private $postData = null;
    private ?int $expectCode = null;
    private ?int $httpResponseCode = null;
    private ?int $customResponseCode = null;

    public function __construct(string $method, string $url, $postData, ?int $expectCode = null, ?int $httpResponseCode = null, ?int $customResponseCode = null)
    {
        $this->time = new DateTime();
        $this->method = $method;
        $this->url = $url;
        $this->postData = $postData;
        $this->expectCode = $expectCode;
        $this->httpResponseCode = $httpResponseCode;
        $this->customResponseCode = $customResponseCode;
    }

    public function getTime(): DateTime
    {
        return $this->time;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getPostData()
    {
        return $this->postData;
    }

    public function getExpectCode(): ?int
    {
        return $this->expectCode;
    }

    public function getHttpResponseCode(): ?int
    {
        return $this->httpResponseCode;
    }

    public function getCustomResponseCode(): ?int
    {
        return $this->customResponseCode;
    }

    public function setTime(DateTime $time)
    {
        $this->time = $time;
        return $this;
    }

    public function setMethod(string $method)
    {
        $this->method = $method;
        return $this;
    }

    public function setUrl(string $url)
    {
        $this->url = $url;
        return $this;
    }

    public function setPostData($postData)
    {
        $this->postData = $postData;
        return $this;
    }

    public function setExpectCode(?int $expectCode)
    {
        $this->expectCode = $expectCode;
        return $this;
    }

    public function setHttpResponseCode(?int $httpResponseCode)
    {
        $this->httpResponseCode = $httpResponseCode;
        return $this;
    }

    public function setCustomResponseCode(?int $customResponseCode)
    {
        $this->customResponseCode = $customResponseCode;
        return $this;
    }
}
