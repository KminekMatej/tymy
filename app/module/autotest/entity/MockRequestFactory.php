<?php

namespace Tymy\Module\Autotest;

use Nette\Http\Request;
use Nette\Http\RequestFactory;
use Nette\Http\Url;
use Nette\Http\UrlScript;
use Nette\Utils\Strings;

/**
 * MockRequestFactory is a class to override default http request factory during tests to be bale tu supply either classic Request object (during standard behaiour) or Mocked http request (during tests)
 */
class MockRequestFactory extends RequestFactory
{
    public function from(string $url, string $method, string $data): Request
    {
        if (!isset($_SERVER['SERVER_NAME'])) {
            $_SERVER['SERVER_NAME'] = "autotest.tymy.cz";
        }

        $request = parent::fromGlobals();

        if (getenv("AUTOTEST")) {
            $rqUrl = $request->getUrl();

            $mockUrl = (new Url($url))
                ->setScheme($rqUrl->getScheme())
                ->setHost($rqUrl->getHost())
                ->setPort($rqUrl->getPort());

            $this->addPathAndQuery($mockUrl, $url);

            $urlScript = new UrlScript($mockUrl, "/");

            return new Request(
                $urlScript,
                [],
                [],
                $request->getCookies(),
                $request->getHeaders(),
                $method,
                $request->getRemoteAddress(),
                $request->getRemoteHost(),
                fn() => $data,
            );
        } else {
            return $request;
        }
    }

    private function addPathAndQuery(Url $url, string $requestUrl): void
    {
        $requestUrl = preg_replace('#^\w++://[^/]++#', '', $requestUrl);
        $requestUrl = Strings::replace($requestUrl, $this->urlFilters['url']);

        $tmp = explode('?', $requestUrl, 2);
        $path = Url::unescape($tmp[0], '%/?#');
        $path = Strings::fixEncoding(Strings::replace($path, $this->urlFilters['path']));
        $url->setPath($path);
        $url->setQuery($tmp[1] ?? '');
    }
}
