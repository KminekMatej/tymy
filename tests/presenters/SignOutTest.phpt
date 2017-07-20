<?php

namespace Test;

use Nette;
use Tester;
use Tester\Assert;

$container = require __DIR__ . '/../bootstrap.php';

if (in_array(basename(__FILE__, '.phpt') , $GLOBALS["testedTeam"]["skips"])) {
    Tester\Environment::skip('Test skipped as set in config file.');
}

class SignOutTest extends IPresenterTest {

    const PRESENTERNAME = "Sign";

    function __construct(Nette\DI\Container $container) {
        $this->container = $container;
    }

    function testSignOutComponents(){
        $this->userTapiAuthenticate($GLOBALS["testedTeam"]["user"], $GLOBALS["testedTeam"]["pass"]);
        $request = new Nette\Application\Request('Sign', 'GET', array('action' => 'out'));
        $response = $this->presenter->run($request);

        Assert::type('Nette\Application\Responses\RedirectResponse', $response);
        Assert::equal('http:///sign/in', substr($response->getUrl(), 0, 15));
        Assert::equal(302, $response->getCode());
        
        Assert::true(!$this->user->isLoggedIn());
    }
    
    /**
     * @throws \Tymy\Exception\APIException
     */
    function testSignOutFails(){
        $this->userTestAuthenticate("Beatles","Ladyda");
        Assert::true($this->user->isLoggedIn());
        $request = new Nette\Application\Request('Sign', 'GET', array('action' => 'out'));
        $response = $this->presenter->run($request);

        Assert::true(!$this->user->isLoggedIn());
    }

}

$test = new SignOutTest($container);
$test->run();
