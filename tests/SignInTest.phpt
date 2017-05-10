<?php

namespace Test;

use Nette;
use Tester;
use Tester\Assert;

$container = require __DIR__ . '/bootstrap.php';

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
        //Assert::true(FALSE);
    }
    
    /**
     * @throws Nette\Security\AuthenticationException Login failed.
     */
    function testSignInFails(){
        $tymyUserManager = new \App\Model\TymyUserManager("dev"); 
        $tymyUserManager->authenticate(["Beatles","Ladyda"]);
        
    }
    
    function testSignInSuccess(){
        $tymyUserManager = new \App\Model\TymyUserManager("dev"); 
        $identity = $tymyUserManager->authenticate([$GLOBALS["username"], $GLOBALS["password"]]);
        
        Assert::type("Nette\Security\Identity", $identity);
        Assert::true(isset($identity->id));
        Assert::true(isset($identity->roles));
        Assert::true(is_array($identity->roles));
        Assert::true(isset($identity->data));
        Assert::true(is_array($identity->data));
        Assert::true(isset($identity->data["id"]));
        Assert::true(isset($identity->data["login"]));
        Assert::true(isset($identity->data["canLogin"]));
        Assert::true(isset($identity->data["lastLogin"]));
        Assert::true(isset($identity->data["status"]));
        Assert::true(isset($identity->data["roles"]));
        Assert::true(is_array($identity->data["roles"]));
        Assert::true(isset($identity->data["oldPassword"]));
        Assert::true(isset($identity->data["firstName"]));
        Assert::true(isset($identity->data["lastName"]));
        Assert::true(isset($identity->data["callName"]));
        Assert::true(isset($identity->data["language"]));
        Assert::true(isset($identity->data["jerseyNumber"]));
        Assert::true(isset($identity->data["street"]));
        Assert::true(isset($identity->data["city"]));
        Assert::true(isset($identity->data["zipCode"]));
        Assert::true(isset($identity->data["phone"]));
        Assert::true(isset($identity->data["phone2"]));
        Assert::true(isset($identity->data["nameDayMonth"]));
        Assert::true(isset($identity->data["nameDayDay"]));
        Assert::true(isset($identity->data["pictureUrl"]));
        Assert::true(isset($identity->data["fullName"]));
        Assert::true(isset($identity->data["displayName"]));
        Assert::true(isset($identity->data["tsid"]));
        Assert::true(isset($identity->data["tym"]));
    }

}

$test = new SignInTest($container);
$test->run();
