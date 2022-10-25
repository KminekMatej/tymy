<?php

namespace Tymy\Module\Autotest;

use Nette\Http\FileUpload;
use Nette\Http\Request;
use Nette\Http\Url;
use Nette\Http\UrlScript;
use Nette\Utils\Arrays;
use Nette\Utils\Strings;

/**
 * MockHttpRequest is a class to override default http request during tests. Into MockHttpRequest, you can inject headers and serve them then to tested requests
 *
 * @author kminekmatej, 1. 9. 2022, 10:06:33
 */
class MockHttpRequest extends Request
{
    private const CHARS = '\x09\x0A\x0D\x20-\x7E\xA0-\x{10FFFF}';

    private ?UrlScript $mockUrl = null;
    private array $mockHeaders = [];
    private array $mockPost = [];
    private array $mockFiles = [];
    private array $mockCookies = [];
    private ?string $mockMethod = null;
    private bool $https = true;
    private string $serverName = "autotest.tymy.cz";
    private string $requestUri;
    private int $serverPort = 443;
    private string $remoteAddr;
    private string $remoteHost;
    private array $urlFilters = [
        'path' => ['#/{2,}#' => '/'], // '%20' => ''
        'url' => [], // '#[.,)]$#D' => ''
    ];

    public function __construct()
    {
        \Tracy\Debugger::barDump(func_get_args());
        parent::__construct(new UrlScript()); //UrlScript is not actually used in parent, everything gets always mocked
    }

    public function getHeaders(): array
    {
        return array_merge(parent::getHeaders(), $this->mockHeaders);
    }

    public function getCookie(string $key): mixed
    {
        return parent::getCookie($key) ?: $this->mockCookies[$key] ?? null;
    }

    public function getCookies(): array
    {
        return array_merge(parent::getCookies(), $this->mockCookies);
    }

    public function getFile($key): ?FileUpload
    {
        $parentFile = parent::getFile($key);

        if (!$parentFile) {
            $res = Arrays::get($this->mockFiles, $key, null);
            return $res instanceof FileUpload ? $res : null;
        }

        return $parentFile;
    }

    public function getFiles(): array
    {
        return array_merge(parent::getFiles(), $this->mockFiles);
    }

    public function getHeader(string $header): ?string
    {
        return parent::getHeader($header) ?: $this->mockHeaders[strtolower($header)] ?? null;
    }

    public function getMethod(): string
    {
        return $this->mockMethod ?: parent::getMethod();
    }

    public function getPost(?string $key = null)
    {
        if (func_num_args() === 0) {
            return array_merge(parent::getPost(), $this->mockPost);
        }

        return $this->mockPost[$key] ?? parent::getPost($key);
    }

    public function getQuery(?string $key = null)
    {
        if (func_num_args() === 0) {
            if ($this->mockUrl) {
                return $this->mockUrl->getQueryParameters();
            }
        }

        if ($this->mockUrl) {
            return $this->mockUrl->getQueryParameter($key);
        }

        return parent::getQuery($key);
    }

    public function getUrl(): UrlScript
    {
        return $this->mockUrl ?? parent::getUrl();
    }

    public function setMockHeaders(array $mockHeaders)
    {
        $this->mockHeaders = array_change_key_case((array) $mockHeaders, CASE_LOWER);
    }

    public function setMockUrl(string $url)
    {
        $this->requestUri = $url;

        $url = new Url();
        $this->getServer($url);
        $this->getPathAndQuery($url);

        //filtering mocked post and cookies
        [$post, $cookies] = $this->getGetPostCookie($url);
        $this->setMockPost($post);
        $this->setMockCookies($cookies);

        $this->mockUrl = new UrlScript($url, $this->getScriptPath($url));

        return $this;
    }

    public function setMockPost(?array $mockPost)
    {
        $this->mockPost = $mockPost;
        return $this;
    }

    public function setMockFiles(?array $mockFiles)
    {
        $this->mockFiles = $mockFiles;
        return $this;
    }

    public function setMockCookies(?array $mockCookies)
    {
        $this->mockCookies = $mockCookies;
        return $this;
    }

    public function setMockMethod(?string $mockMethod)
    {
        $this->mockMethod = $mockMethod;
        return $this;
    }

    public function setHttps(bool $https)
    {
        $this->https = $https;
        return $this;
    }

    public function setServerName(string $serverName)
    {
        $this->serverName = $serverName;
        return $this;
    }

    public function setRequestUri(string $requestUri)
    {
        $this->requestUri = $requestUri;
        return $this;
    }

    public function setServerPort(int $serverPort)
    {
        $this->serverPort = $serverPort;
        return $this;
    }

    public function setRemoteAddr(string $remoteAddr)
    {
        $this->remoteAddr = trim($remoteAddr, '[]');// workaround for PHP 7.3
        return $this;
    }

    public function setRemoteHost(string $remoteHost)
    {
        $this->remoteHost = $remoteHost;
        return $this;
    }

    public function setUrlFilters(array $urlFilters)
    {
        $this->urlFilters = $urlFilters;
        return $this;
    }

    public function clearMocks()
    {
        $this->mockFiles = [];
        $this->mockPost = [];
        $this->mockCookies = [];
        $this->mockHeaders = [];
        $this->mockMethod = null;
        $this->mockUrl = null;
    }

    //**************** mock helpers ****************//


    private function getServer(Url $url): void
    {
        $serverName = getenv("SERVER_NAME") ?: $this->serverName;

        $url->setScheme($this->https ? 'https' : 'http');

        if (preg_match('#^([a-z0-9_.-]+|\[[a-f0-9:]+\])(:\d+)?$#Di', $serverName, $pair)) {
            $url->setHost(strtolower($pair[1]));
            if (isset($pair[2])) {
                $url->setPort((int) substr($pair[2], 1));
            } elseif (isset($this->serverPort)) {
                $url->setPort((int) $this->serverPort);
            }
        }
    }

    private function getPathAndQuery(Url $url): void
    {
        $requestUrl = $this->requestUri ? "/api{$this->requestUri}" : '/';
        $requestUrl = preg_replace('#^\w++://[^/]++#', '', $requestUrl);
        $requestUrl = Strings::replace($requestUrl, $this->urlFilters['url']);

        $tmp = explode('?', $requestUrl, 2);
        $path = Url::unescape($tmp[0], '%/?#');
        $path = Strings::fixEncoding(Strings::replace($path, $this->urlFilters['path']));
        $url->setPath($path);
        $url->setQuery($tmp[1] ?? '');
    }

    private function getGetPostCookie(Url $url): array
    {
        $useFilter = (!in_array(ini_get('filter.default'), ['', 'unsafe_raw'], true) || ini_get('filter.default_flags'));

        $query = $url->getQueryParameters();

        $post = $useFilter ? filter_var($this->mockPost, FILTER_UNSAFE_RAW) : (empty($this->mockPost) ? [] : $this->mockPost);
        $cookies = $useFilter ? filter_var($this->mockCookies, FILTER_UNSAFE_RAW) : (empty($this->mockCookies) ? [] : $this->mockCookies);

        // remove invalid characters
        $reChars = '#^[' . self::CHARS . ']*+$#Du';
        $list = [&$query, &$post, &$cookies];
        foreach ($list as $key => &$val) {
            foreach ($val as $k => $v) {
                if (is_string($k) && (!preg_match($reChars, $k) || preg_last_error())) {
                    unset($list[$key][$k]);
                } elseif (is_array($v)) {
                    $list[$key][$k] = $v;
                    $list[] = &$list[$key][$k];
                } else {
                    $list[$key][$k] = (string) preg_replace('#[^' . self::CHARS . ']+#u', '', (string) $v);
                }
            }
        }
        unset($list, $key, $val, $k, $v);

        $url->setQuery($query);
        return [$post, $cookies];
    }

    private function getScriptPath(Url $url): string
    {
        $path = $url->getPath();
        $lpath = strtolower($path);
        $script = "/api/www/index.php";
        if ($lpath !== $script) {
            $max = min(strlen($lpath), strlen($script));
            for ($i = 0; $i < $max && $lpath[$i] === $script[$i]; $i++)
                ;
            $path = $i ? substr($path, 0, strrpos($path, '/', $i - strlen($path) - 1) + 1) : '/';
        }
        return $path;
    }
}
