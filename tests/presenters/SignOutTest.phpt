<?php

namespace Test;

use Nette;
use Nette\Application\Request;
use Tester\Assert;
use Tester\Environment;

$container = require __DIR__ . '/../bootstrap.php';

if (in_array(basename(__FILE__, '.phpt') , $GLOBALS["testedTeam"]["skips"])) {
    Environment::skip('Test skipped as set in config file.');
}

class SignOutTest extends IPresenterTest {

    const PRESENTERNAME = "Sign";

    function __construct(Nette\DI\Container $container) {
        $this->container = $container;
    }

    function testSignOutComponents(){
        $this->userTapiAuthenticate($GLOBALS["testedTeam"]["user"], $GLOBALS["testedTeam"]["pass"]);
        $request = new Request('Sign', 'GET', array('action' => 'out'));
        $response = $this->presenter->run($request);

        Assert::type('Nette\Application\Responses\RedirectResponse', $response);
        Assert::equal("/sign/in", parse_url($response->getUrl(), PHP_URL_PATH));
        Assert::equal(302, $response->getCode());
        
        Assert::true(!$this->user->isLoggedIn());
    }
    
    /**
     * @throws \Tapi\Exception\APIAuthenticationException
     */
    function testSignOutFails(){
        $this->userTestAuthenticate("Beatles","Ladyda");
        Assert::true($this->user->isLoggedIn());
        $request = new Request('Sign', 'GET', array('action' => 'out'));
        $response = $this->presenter->run($request);

        Assert::true(!$this->user->isLoggedIn());
    }

}

$test = new SignOutTest($container);
$test->run();
