<?php
/**
 * TEST: Test Tymy main class on TYMY api
 * 
  */

namespace Test;

use Nette;
use Tester;
use Tester\Assert;

$container = require __DIR__ . '/bootstrap.php';
if (in_array(basename(__FILE__, '.phpt') , $GLOBALS["testedTeam"]["skips"])) {
    Tester\Environment::skip('Test skipped as set in config file.');
}

class APITymyTest extends Tester\TestCase {

    private $container;
    private $tymyObj;
    private $authenticator;

    function __construct(Nette\DI\Container $container) {
        $this->container = $container;
    }

    function setUp() {
        parent::setUp();
        $this->tymyObj = new \Tymy\Login();
        $this->authenticator = new \App\Model\TestAuthenticator();
    }
    
    function tearDown() {
        parent::tearDown();
    }
    
    function login(){
        $tymyObj = new \Tymy\Login();
        $this->tymyObj = $tymyObj->team($GLOBALS["testedTeam"]["team"])
                ->setUsername($GLOBALS["testedTeam"]["user"])
                ->setPassword($GLOBALS["testedTeam"]["pass"])
                ->fetch();
    }
    
    function testObj(){
        Assert::true(is_object($this->tymyObj));
        Assert::null($this->tymyObj->getResult());
        Assert::null($this->tymyObj->getData());
        Assert::type("\Nette\Reflection\ClassType", $this->tymyObj->getReflection());
    }

    function testTeam(){
        Assert::null($this->tymyObj->team);
        $this->tymyObj->team($GLOBALS["testedTeam"]["team"]);
        Assert::type("string", $this->tymyObj->team);
        Assert::same($this->tymyObj->team, $GLOBALS["testedTeam"]["team"]);
    }
    
    function testPresenterSet(){
        $presenterFactory = $this->container->getByType('Nette\Application\IPresenterFactory');
        $mockPresenter = $presenterFactory->createPresenter('Homepage');
        $mockPresenter->autoCanonicalize = FALSE;
        
        $this->authenticator->setId(38);
        $this->authenticator->setStatus(["TESTROLE", "TESTROLE2"]);
        $this->authenticator->setArr(["tym" => "testteam","sessionKey" => "dsfbglsdfbg13546"]);
        
        $mockPresenter->getUser()->setAuthenticator($this->authenticator);
        $mockPresenter->getUser()->login("test","test");
        $this->tymyObj->presenter($mockPresenter);
        
        Assert::type("string", $this->tymyObj->team);
        Assert::same($this->tymyObj->team, "testteam");
        
        Assert::type("array", $this->tymyObj->getUriParams());
        Assert::true(array_key_exists("TSID", $this->tymyObj->getUriParams()));
        Assert::contains("dsfbglsdfbg13546", $this->tymyObj->getUriParams());
    }
    
    function testProtocols(){
        Assert::type("string", $this->tymyObj->getProtocol());
        Assert::same($this->tymyObj->getProtocol(), "http");
        Assert::notsame($this->tymyObj->getProtocol(), "https");
        
        $this->tymyObj->https(TRUE);
        
        Assert::type("string", $this->tymyObj->getProtocol());
        Assert::same($this->tymyObj->getProtocol(), "https");
        Assert::notsame($this->tymyObj->getProtocol(), "http");
    }

}

$test = new APITymyTest($container);
$test->run();
