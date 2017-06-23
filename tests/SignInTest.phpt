<?php

namespace Test;

use Nette;
use Tester;
use Tester\Assert;

$container = require __DIR__ . '/bootstrap.php';
if (in_array(basename(__FILE__, '.phpt') , $GLOBALS["testedTeam"]["skips"])) {
    Tester\Environment::skip('Test skipped as set in config file.');
}

class SignInTest extends Tester\TestCase {

    private $container;
    private $presenter;

    function __construct(Nette\DI\Container $container) {
        $this->container = $container;
    }

    function setUp() {
        $presenterFactory = $this->container->getByType('Nette\Application\IPresenterFactory');
        $this->presenter = $presenterFactory->createPresenter('Sign');
        $this->presenter->autoCanonicalize = FALSE;
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
    }
    
    /**
     * @throws Nette\Security\AuthenticationException Login failed.
     */
    function testSignInFails(){
        $tymyUserManager = new \App\Model\TymyUserManager($GLOBALS["testedTeam"]["team"]); 
        $tymyUserManager->authenticate(["Beatles","Ladyda"]);
        
    }
    
    function testSignInSuccess(){
        $tymyUserManager = new \App\Model\TymyUserManager($GLOBALS["testedTeam"]["team"]); 
        $identity = $tymyUserManager->authenticate([$GLOBALS["testedTeam"]["user"], $GLOBALS["testedTeam"]["pass"]]);
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
        Assert::true(isset($identity->data["data"]->oldPassword));
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
