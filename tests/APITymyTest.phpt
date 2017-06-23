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

class APITymyTest extends TapiTestCase {

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
    
    function testObj(){
        Assert::true(is_object($this->tymyObj));
        Assert::null($this->tymyObj->getResult());
        Assert::null($this->tymyObj->getData());
        Assert::type("\Nette\Reflection\ClassType", $this->tymyObj->getReflection());
    }
    
    function testPresenterSet(){
        $presenterFactory = $this->container->getByType('Nette\Application\IPresenterFactory');
        $mockPresenter = $presenterFactory->createPresenter('Homepage');
        $mockPresenter->autoCanonicalize = FALSE;
        
        $this->authenticator->setId(38);
        $this->authenticator->setStatus(["TESTROLE", "TESTROLE2"]);
        $this->authenticator->setArr(["sessionKey" => "dsfbglsdfbg13546"]);
        
        $mockPresenter->getUser()->setAuthenticator($this->authenticator);
        $mockPresenter->getUser()->login("test","test");
        $this->tymyObj->setPresenter($mockPresenter);
        
        Assert::type("array", $this->tymyObj->getUriParams());
        Assert::true(array_key_exists("TSID", $this->tymyObj->getUriParams()));
        Assert::contains("dsfbglsdfbg13546", $this->tymyObj->getUriParams());
    }
}

$test = new APITymyTest($container);
$test->run();
