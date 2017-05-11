<?php
/**
 * TEST: Test Discussions on TYMY api
 * 
 */

namespace Test;

use Nette;
use Tester;
use Tester\Assert;

$container = require __DIR__ . '/bootstrap.php';

class APIDiscussionsTest extends Tester\TestCase {

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
        $this->login = $this->loginObj->team("dev")
                ->setUsername($GLOBALS["username"])
                ->setPassword($GLOBALS["password"])
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


        $discussionsObj = new \Tymy\Discussions(NULL);
        $discussionsObj->presenter($mockPresenter)
                ->recId(1)
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
        $this->authenticator->setArr(["tym" => "dev", "sessionKey" => "dsfbglsdfbg13546"]);

        $mockPresenter->getUser()->setAuthenticator($this->authenticator);
        $mockPresenter->getUser()->login("test", "test");


        $discussionsObj = new \Tymy\Discussions(NULL);
        $discussionsObj
                ->presenter($mockPresenter)
                ->recId(1)
                ->fetch();
    }
    
    function testFetchSuccess() {
        $presenterFactory = $this->container->getByType('Nette\Application\IPresenterFactory');
        $mockPresenter = $presenterFactory->createPresenter('Discussion');
        $mockPresenter->autoCanonicalize = FALSE;

        $this->login();
        $this->authenticator->setId($this->login->id);
        $this->authenticator->setArr(["tym" => "dev", "sessionKey" => $this->loginObj->getResult()->sessionKey]);
        $mockPresenter->getUser()->setAuthenticator($this->authenticator);
        $mockPresenter->getUser()->setExpiration('2 minutes');
        $mockPresenter->getUser()->login($GLOBALS["username"], $GLOBALS["password"]);

        $discussionsObj = new \Tymy\Discussions($mockPresenter);
        $discussionsObj->recId(1)
                ->fetch();
        Assert::true(is_object($discussionsObj));
        Assert::true(is_object($discussionsObj->result));
        Assert::type("string",$discussionsObj->result->status);
        Assert::same("OK",$discussionsObj->result->status);
        Assert::true(is_object($discussionsObj->result->data[0]));//returned discussion object
        Assert::type("int",$discussionsObj->result->data[0]->id);
        Assert::true(!property_exists($discussionsObj->result->data[0],"newInfo"));//returned discussion object
    }
    
    function testFetchWithNew() {
        $presenterFactory = $this->container->getByType('Nette\Application\IPresenterFactory');
        $mockPresenter = $presenterFactory->createPresenter('Discussion');
        $mockPresenter->autoCanonicalize = FALSE;

        $this->login();
        $this->authenticator->setId($this->login->id);
        $this->authenticator->setArr(["tym" => "dev", "sessionKey" => $this->loginObj->getResult()->sessionKey]);
        $mockPresenter->getUser()->setAuthenticator($this->authenticator);
        $mockPresenter->getUser()->setExpiration('2 minutes');
        $mockPresenter->getUser()->login($GLOBALS["username"], $GLOBALS["password"]);

        $discussionsObj = new \Tymy\Discussions($mockPresenter);
        $discussionsObj->recId(1)
                ->setWithNew(TRUE)
                ->fetch();

        Assert::true(is_object($discussionsObj));
        Assert::true(is_object($discussionsObj->result));
        Assert::type("string",$discussionsObj->result->status);
        Assert::same("OK",$discussionsObj->result->status);
        Assert::true(is_object($discussionsObj->result->data[0]));
        Assert::type("int",$discussionsObj->result->data[0]->id);
        Assert::true(is_object($discussionsObj->result->data[0]->newInfo));
        Assert::type("int", $discussionsObj->result->data[0]->newInfo->newsCount);
        Assert::type("int", $discussionsObj->result->data[0]->newInfo->discussionId);
        Assert::type("string", $discussionsObj->result->data[0]->newInfo->lastVisit);
    }

}

$test = new APIDiscussionsTest($container);
$test->run();
