<?php

declare(strict_types=1);

namespace Tymy\Module\Autotest;

use Nette;
use Nette\Http\FileUpload;
use Nette\Http\Helpers;
use Nette\Http\Request;
use Nette\Http\Url;
use Nette\Http\UrlScript;
use Nette\Utils\Strings;

/**
 * HTTP request factory.
 */
class RequestMockFactory
{
    use Nette\SmartObject;

    /** @internal */
    private const CHARS = '\x09\x0A\x0D\x20-\x7E\xA0-\x{10FFFF}';

    /** @var array */
    public $urlFilters = [
        'path' => ['#/{2,}#' => '/'], // '%20' => ''
        'url' => [], // '#[.,)]$#D' => ''
    ];

    /** @var bool */
    private $binary = false;

    /** @var array */
    private $SERVERMOCK = [];

    /** @var array */
    private $POSTMOCK = [];

    /** @var array */
    private $FILESMOCK = [];

    /** @var string[] */
    private $proxies = [];


    /**
     * @return static
     */
    public function setBinary(bool $binary = true)
    {
        $this->binary = $binary;
        return $this;
    }


    /**
     * @param  string|string[]  $proxy
     * @return static
     */
    public function setProxy($proxy)
    {
        $this->proxies = (array) $proxy;
        return $this;
    }


    /**
     * Returns new Request instance, using values from superglobals.
     */
    public function fromMock($SERVERMOCK, $POSTMOCK = [], $COOKIESMOCK = [], $FILESMOCK = [], ?string $rawInput = null): Request
    {
        $this->SERVERMOCK = $SERVERMOCK;
        $this->POSTMOCK = is_array($POSTMOCK) ? $POSTMOCK : null;
        $this->FILESMOCK = $FILESMOCK;

        $url = new Url();
        $this->getServer($url);
        $this->getPathAndQuery($url);
        $this->getUserAndPassword($url);
        [$post, $cookies] = $this->getGetPostCookie($url);
        [$remoteAddr, $remoteHost] = $this->getClient($url);

        return new Request(
            new UrlScript($url, $this->getScriptPath($url)),
            $post,
            $this->getFiles(),
            $cookies,
            $this->getHeaders(),
            $this->getMethod(),
            $remoteAddr,
            $remoteHost,
            $rawInput
        );
    }


    private function getServer(Url $url): void
    {
        $url->setScheme(!empty($this->SERVERMOCK['HTTPS']) && strcasecmp($this->SERVERMOCK['HTTPS'], 'off') ? 'https' : 'http');

        if (
            (isset($this->SERVERMOCK[$tmp = 'HTTP_HOST']) || isset($this->SERVERMOCK[$tmp = 'SERVER_NAME']))
            && preg_match('#^([a-z0-9_.-]+|\[[a-f0-9:]+\])(:\d+)?$#Di', $this->SERVERMOCK[$tmp], $pair)
        ) {
            $url->setHost(strtolower($pair[1]));
            if (isset($pair[2])) {
                $url->setPort((int) substr($pair[2], 1));
            } elseif (isset($this->SERVERMOCK['SERVER_PORT'])) {
                $url->setPort((int) $this->SERVERMOCK['SERVER_PORT']);
            }
        }
    }


    private function getPathAndQuery(Url $url): void
    {
        $requestUrl = $this->SERVERMOCK['REQUEST_URI'] ?? '/';
        $requestUrl = preg_replace('#^\w++://[^/]++#', '', $requestUrl);
        $requestUrl = Strings::replace($requestUrl, $this->urlFilters['url']);

        $tmp = explode('?', $requestUrl, 2);
        $path = Url::unescape($tmp[0], '%/?#');
        $path = Strings::fixEncoding(Strings::replace($path, $this->urlFilters['path']));
        $url->setPath($path);
        $url->setQuery($tmp[1] ?? '');
    }


    private function getUserAndPassword(Url $url): void
    {
        $url->setUser($this->SERVERMOCK['PHP_AUTH_USER'] ?? '');
        $url->setPassword($this->SERVERMOCK['PHP_AUTH_PW'] ?? '');
    }


    private function getScriptPath(Url $url): string
    {
        $path = $url->getPath();
        $lpath = strtolower($path);
        $script = strtolower($this->SERVERMOCK['SCRIPT_NAME'] ?? '');
        if ($lpath !== $script) {
            $max = min(strlen($lpath), strlen($script));
            $path = $i !== 0 ? substr($path, 0, strrpos($path, '/', $i - strlen($path) - 1) + 1) : '/';
        }
        return $path;
    }


    private function getGetPostCookie(Url $url): array
    {
        $query = $url->getQueryParameters();
        $post = empty($this->POSTMOCK) ? [] : $this->POSTMOCK;
        $cookies = empty($this->COOKIEMOCK) ? [] : $this->COOKIEMOCK;

        // remove invalid characters
        $reChars = '#^[' . self::CHARS . ']*+$#Du';
        if (!$this->binary) {
            $list = [&$query, &$post, &$cookies];
            foreach ($list as $key => &$val) {
                foreach ($val as $k => $v) {
                    if (is_string($k) && (!preg_match($reChars, $k) || preg_last_error())) {
                        unset($list[$key][$k]);
                    } elseif (is_array($v)) {
                        $list[$key][$k] = $v;
                        $list[] = &$list[$key][$k];
                    } else {
                        $list[$key][$k] = (string) preg_replace('#[^' . self::CHARS . ']+#u', '', $v);
                    }
                }
            }
            unset($list, $key, $val, $k, $v);
        }

        $url->setQuery($query);
        return [$post, $cookies];
    }


    private function getFiles(): array
    {
        $reChars = '#^[' . self::CHARS . ']*+$#Du';
        $files = [];
        $list = [];
        foreach ($this->FILESMOCK ?? [] as $k => $v) {
            if (
                !is_array($v)
                || !isset($v['name'], $v['type'], $v['size'], $v['tmp_name'], $v['error'])
                || (!$this->binary && is_string($k) && (!preg_match($reChars, $k) || preg_last_error()))
            ) {
                continue;
            }
            $v['@'] = &$files[$k];
            $list[] = $v;
        }

        // create FileUpload objects
        foreach ($list as &$v) {
            if (!isset($v['name'])) {
                continue;
            } elseif (!is_array($v['name'])) {
                if (!$this->binary && (!preg_match($reChars, $v['name']) || preg_last_error())) {
                    $v['name'] = '';
                }
                if ($v['error'] !== UPLOAD_ERR_NO_FILE) {
                    $v['@'] = new FileUpload($v);
                }
                continue;
            }

            foreach (array_keys($v['name']) as $k) {
                if (!$this->binary && is_string($k) && (!preg_match($reChars, $k) || preg_last_error())) {
                    continue;
                }
                $list[] = [
                    'name' => $v['name'][$k],
                    'type' => $v['type'][$k],
                    'size' => $v['size'][$k],
                    'tmp_name' => $v['tmp_name'][$k],
                    'error' => $v['error'][$k],
                    '@' => &$v['@'][$k],
                ];
            }
        }
        return $files;
    }


    private function getHeaders(): array
    {
        if (function_exists('apache_request_headers')) {
            return apache_request_headers();
        }

        $headers = [];
        foreach ($this->SERVERMOCK as $k => $v) {
            if (strncmp($k, 'HTTP_', 5) == 0) {
                $k = substr($k, 5);
            } elseif (strncmp($k, 'CONTENT_', 8) !== 0) {
                continue;
            }
            $headers[strtr($k, '_', '-')] = $v;
        }
        return $headers;
    }


    private function getMethod(): ?string
    {
        $method = $this->SERVERMOCK['REQUEST_METHOD'] ?? null;
        if (
            $method === 'POST'
            && preg_match('#^[A-Z]+$#D', $this->SERVERMOCK['HTTP_X_HTTP_METHOD_OVERRIDE'] ?? '')
        ) {
            $method = $this->SERVERMOCK['HTTP_X_HTTP_METHOD_OVERRIDE'];
        }
        return $method;
    }


    private function getClient(Url $url): array
    {
        $remoteAddr = empty($this->SERVERMOCK['REMOTE_ADDR']) ? null : trim($this->SERVERMOCK['REMOTE_ADDR'], '[]'); // workaround for PHP 7.3
        $remoteHost = empty($this->SERVERMOCK['REMOTE_HOST']) ? null : $this->SERVERMOCK['REMOTE_HOST'];

        // use real client address and host if trusted proxy is used
        $usingTrustedProxy = $remoteAddr && array_filter($this->proxies, function (string $proxy) use ($remoteAddr): bool {
            return Helpers::ipMatch($remoteAddr, $proxy);
        });
        if ($usingTrustedProxy) {
            empty($this->SERVERMOCK['HTTP_FORWARDED'])
                ? $this->useNonstandardProxy($url, $remoteAddr, $remoteHost)
                : $this->useForwardedProxy($url, $remoteAddr, $remoteHost);
        }

        return [$remoteAddr, $remoteHost];
    }


    private function useForwardedProxy(Url $url, &$remoteAddr, &$remoteHost): void
    {
        $forwardParams = preg_split('/[,;]/', $this->SERVERMOCK['HTTP_FORWARDED']);
        foreach ($forwardParams as $forwardParam) {
            [$key, $value] = explode('=', $forwardParam, 2) + [1 => null];
            $proxyParams[strtolower(trim($key))][] = trim($value, " \t\"");
        }

        if (isset($proxyParams['for'])) {
            $address = $proxyParams['for'][0];
            if (strpos($address, '[') === false) { //IPv4
                $remoteAddr = explode(':', $address)[0];
            } else { //IPv6
                $remoteAddr = substr($address, 1, strpos($address, ']') - 1);
            }
        }

        if (isset($proxyParams['host']) && count($proxyParams['host']) === 1) {
            $host = $proxyParams['host'][0];
            $startingDelimiterPosition = strpos($host, '[');
            if ($startingDelimiterPosition === false) { //IPv4
                $remoteHostArr = explode(':', $host);
                $remoteHost = $remoteHostArr[0];
                $url->setHost($remoteHost);
                if (isset($remoteHostArr[1])) {
                    $url->setPort((int) $remoteHostArr[1]);
                }
            } else { //IPv6
                $endingDelimiterPosition = strpos($host, ']');
                $remoteHost = substr($host, strpos($host, '[') + 1, $endingDelimiterPosition - 1);
                $url->setHost($remoteHost);
                $remoteHostArr = explode(':', substr($host, $endingDelimiterPosition));
                if (isset($remoteHostArr[1])) {
                    $url->setPort((int) $remoteHostArr[1]);
                }
            }
        }

        $scheme = (isset($proxyParams['proto']) && count($proxyParams['proto']) === 1) ? $proxyParams['proto'][0] : 'http';
        $url->setScheme(strcasecmp($scheme, 'https') === 0 ? 'https' : 'http');
    }


    private function useNonstandardProxy(Url $url, &$remoteAddr, &$remoteHost): void
    {
        if (!empty($this->SERVERMOCK['HTTP_X_FORWARDED_PROTO'])) {
            $url->setScheme(strcasecmp($this->SERVERMOCK['HTTP_X_FORWARDED_PROTO'], 'https') === 0 ? 'https' : 'http');
            $url->setPort($url->getScheme() === 'https' ? 443 : 80);
        }

        if (!empty($this->SERVERMOCK['HTTP_X_FORWARDED_PORT'])) {
            $url->setPort((int) $this->SERVERMOCK['HTTP_X_FORWARDED_PORT']);
        }

        if (!empty($this->SERVERMOCK['HTTP_X_FORWARDED_FOR'])) {
            $xForwardedForWithoutProxies = array_filter(explode(',', $this->SERVERMOCK['HTTP_X_FORWARDED_FOR']), function (string $ip): bool {
                return !array_filter($this->proxies, function (string $proxy) use ($ip): bool {
                    return filter_var(trim($ip), FILTER_VALIDATE_IP) !== false && Helpers::ipMatch(trim($ip), $proxy);
                });
            });
            $remoteAddr = trim(end($xForwardedForWithoutProxies));
            $xForwardedForRealIpKey = key($xForwardedForWithoutProxies);
        }

        if (isset($xForwardedForRealIpKey) && !empty($this->SERVERMOCK['HTTP_X_FORWARDED_HOST'])) {
            $xForwardedHost = explode(',', $this->SERVERMOCK['HTTP_X_FORWARDED_HOST']);
            if (isset($xForwardedHost[$xForwardedForRealIpKey])) {
                $remoteHost = trim($xForwardedHost[$xForwardedForRealIpKey]);
                $url->setHost($remoteHost);
            }
        }
    }
}
