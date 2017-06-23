<?php
/**
 * TEST: Test Events on TYMY api
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

class APIEventTypesTest extends Tester\TestCase {

    private $container;
    private $login;
    private $loginObj;
    private $authenticator;

    function __construct(Nette\DI\Container $container) {
        $this->container = $container;
    }

    function setUp() {
        parent::setUp();
        $this->authenticator = new \App\Model\TestAuthenticator();
    }
    
    function tearDown() {
        parent::tearDown();
    }
    
    function login(){
        $this->loginObj = new \Tymy\Login();
        $this->login = $this->loginObj->team($GLOBALS["testedTeam"]["team"])
                ->setUsername($GLOBALS["testedTeam"]["user"])
                ->setPassword($GLOBALS["testedTeam"]["pass"])
                ->fetch();
    }

    /**
     * @throws Tymy\Exception\APIException
     */
    function testFetchNotLoggedInFails404() {
        $presenterFactory = $this->container->getByType('Nette\Application\IPresenterFactory');
        $mockPresenter = $presenterFactory->createPresenter('Homepage');
        $mockPresenter->autoCanonicalize = FALSE;

        $this->authenticator->setId(38);
        $this->authenticator->setStatus(["TESTROLE", "TESTROLE2"]);
        $this->authenticator->setArr(["tym" => "testteam", "sessionKey" => "dsfbglsdfbg13546"]);

        $mockPresenter->getUser()->setAuthenticator($this->authenticator);
        $mockPresenter->getUser()->login("test", "test");


        $eventTypesObj = new \Tymy\EventTypes();
        $eventTypesObj->setPresenter($mockPresenter)
                ->fetch();
    }
    
    /**
     * @throws Nette\Application\AbortException
     */
    function testFetchNotLoggedInRedirects() {
        $presenterFactory = $this->container->getByType('Nette\Application\IPresenterFactory');
        $mockPresenter = $presenterFactory->createPresenter('Homepage');
        $mockPresenter->autoCanonicalize = FALSE;

        $this->authenticator->setId(38);
        $this->authenticator->setStatus(["TESTROLE", "TESTROLE2"]);
        $this->authenticator->setArr(["tym" => $GLOBALS["testedTeam"]["team"], "sessionKey" => "dsfbglsdfbg13546"]);

        $mockPresenter->getUser()->setAuthenticator($this->authenticator);
        $mockPresenter->getUser()->login("test", "test");


        $eventTypesObj = new \Tymy\EventTypes();
        $eventTypesObj->setPresenter($mockPresenter)
                ->fetch();
    }
    
    function testFetchSuccess() {
        $presenterFactory = $this->container->getByType('Nette\Application\IPresenterFactory');
        $mockPresenter = $presenterFactory->createPresenter('Event');
        $mockPresenter->autoCanonicalize = FALSE;

        $this->login();
        $this->authenticator->setId($this->login->id);
        $this->authenticator->setArr(["tym" => $GLOBALS["testedTeam"]["team"], "sessionKey" => $this->loginObj->getResult()->sessionKey]);
        $mockPresenter->getUser()->setAuthenticator($this->authenticator);
        $mockPresenter->getUser()->setExpiration('2 minutes');
        $mockPresenter->getUser()->login($GLOBALS["testedTeam"]["user"], $GLOBALS["testedTeam"]["pass"]);

        $eventTypesObj = new \Tymy\EventTypes($mockPresenter->tapiAuthenticator, $mockPresenter);
        $eventTypesObj->fetch();
        
        Assert::same(1, count($eventTypesObj->getUriParams()));
        
        Assert::true(is_object($eventTypesObj));
        Assert::true(is_object($eventTypesObj->result));
        Assert::type("string",$eventTypesObj->result->status);
        Assert::same("OK",$eventTypesObj->result->status);
        
        Assert::type("array",$eventTypesObj->result->data);
        
        foreach ($eventTypesObj->result->data as $evt) {
            Assert::true(is_object($evt));
            Assert::type("int",$evt->id);
            Assert::true($evt->id > 0);
            Assert::type("string",$evt->code);
            Assert::type("string",$evt->caption);
            Assert::type("int",$evt->preStatusSetId);
            Assert::true($evt->preStatusSetId > 0);
            Assert::type("int",$evt->postStatusSetId);
            Assert::true($evt->postStatusSetId > 0);
            Assert::type("array",$evt->preStatusSet);
            foreach ($evt->preStatusSet as $evtSS) {
                Assert::type("int",$evtSS->id);
                Assert::true($evtSS->id > 0);
                Assert::type("string",$evtSS->code);
                Assert::type("string",$evtSS->caption);
            }
            Assert::type("array",$evt->postStatusSet);
            foreach ($evt->postStatusSet as $evtSS) {
                Assert::type("int",$evtSS->id);
                Assert::true($evtSS->id > 0);
                Assert::type("string",$evtSS->code);
                Assert::type("string",$evtSS->caption);
            }
        }
    }
}

$test = new APIEventTypesTest($container);
$test->run();
