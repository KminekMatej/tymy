<?php

namespace Test;

use Nette;
use Tester;
use Tester\Assert;

$container = require __DIR__ . '/../bootstrap.php';

if (in_array(basename(__FILE__, '.phpt') , $GLOBALS["testedTeam"]["skips"])) {
    Tester\Environment::skip('Test skipped as set in config file.');
}

class SignInTest extends IPresenterTest {

    const PRESENTERNAME = "Sign";

    function __construct(Nette\DI\Container $container) {
        $this->container = $container;
    }

    function testSignInComponents(){
        $request = new Nette\Application\Request('Sign', 'GET', array('action' => 'in'));
        $response = $this->presenter->run($request);

        Assert::type('Nette\Application\Responses\TextResponse', $response);
        Assert::type('Nette\Bridges\ApplicationLatte\Template', $response->getSource());

        $html = (string) $response->getSource();

        $dom = Tester\DomQuery::fromHtml($html);

        Assert::true($dom->has('input[name="name"]'));
        Assert::true($dom->has('input[name="password"]'));
        Assert::true($dom->has('input[name="send"]'));
        if($this->supplier->getTapi_config()["multiple_team"]){
            Assert::true($dom->has('select[name="team"]'));
        }
        
        
        Assert::true($dom->has('a[href="/sign/up"]'));
        Assert::true($dom->has('a[href="/sign/pwdLost"]'));
    }
    
    /**
     * @throws Tymy\Exception\APIAuthenticationException Login failed. Wrong username or password.
     */
    function testSignInFails(){
        $this->userTapiAuthenticate("Beatles","Ladyda");
    }
    
    function testSignInSuccess(){
        $this->userTapiAuthenticate($GLOBALS["testedTeam"]["user"], $GLOBALS["testedTeam"]["pass"]);
        $identity = $this->user->getIdentity();
        Assert::type("Nette\Security\Identity", $identity);
        Assert::true(isset($identity->id));
        Assert::true(isset($identity->roles));
        Assert::true(is_array($identity->roles));
        Assert::true(isset($identity->data["sessionKey"]));
        Assert::equal(strlen($identity->data["sessionKey"]), 28);
        Assert::true(isset($identity->data));
        Assert::true(is_array($identity->data));
        Assert::true(isset($identity->data["data"]->id));
        Assert::true(isset($identity->data["data"]->login));
        Assert::true(isset($identity->data["data"]->canLogin));
        Assert::true(isset($identity->data["data"]->lastLogin));
        Assert::true(isset($identity->data["data"]->status));
        Assert::true(isset($identity->data["data"]->roles));
        Assert::true(is_array($identity->data["data"]->roles));
        Assert::true(isset($identity->data["data"]->firstName));
        Assert::true(isset($identity->data["data"]->lastName));
        Assert::true(isset($identity->data["data"]->callName));
        Assert::true(isset($identity->data["data"]->language));
        Assert::true(isset($identity->data["data"]->jerseyNumber));
        Assert::true(isset($identity->data["data"]->street));
        Assert::true(isset($identity->data["data"]->city));
        Assert::true(isset($identity->data["data"]->zipCode));
        Assert::true(isset($identity->data["data"]->phone));
        Assert::true(isset($identity->data["data"]->phone2));
        Assert::true(isset($identity->data["data"]->nameDayMonth));
        Assert::true(isset($identity->data["data"]->nameDayDay));
        Assert::true(isset($identity->data["data"]->pictureUrl));
        Assert::true(isset($identity->data["data"]->fullName));
        Assert::true(isset($identity->data["data"]->displayName));
    }

}

$test = new SignInTest($container);
$test->run();
