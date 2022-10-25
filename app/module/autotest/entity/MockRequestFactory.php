<?php

namespace Tymy\Module\Autotest;

use Nette\Http\Request;
use Nette\Http\RequestFactory;

/**
 * MockRequestFactory is a class to override default http request factory during tests to be bale tu supply either classic Request object (during standard behaiour) or Mocked http request (during tests)
 *
 * @author kminekmatej, 25. 10. 2022, 21:57:33
 */
class MockRequestFactory extends RequestFactory
{
    public function fromGlobals(): Request
    {
        if (getenv("AUTOTEST")) {
            return new MockHttpRequest();
        } else {
            return parent::fromGlobals();
        }
    }
}
