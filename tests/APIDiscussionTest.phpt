<?php
/**
 * TEST: Test Discussion on TYMY api
 * 
 */

namespace Test;

use Nette;
use Tester;
use Tester\Assert;

$container = require __DIR__ . '/bootstrap.php';

class APIDiscussionTest extends Tester\TestCase {

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
    function testFetchFailsNoRecId(){
        $discussionObj = new \Tymy\Discussion(NULL, TRUE, 1);
        $discussion = $discussionObj
                ->team("dev")
                ->fetch();
    }

    /**
     * @throws Tymy\Exception\APIException
     */
    function testFetchFailsPageDoNotExist(){
        $discussionObj = new \Tymy\Discussion(NULL, TRUE, -1);
        $discussion = $discussionObj
                ->team("dev")
                ->recId(1)
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


        $discussionObj = new \Tymy\Discussion(NULL, TRUE, 1);
        $discussionObj
                ->presenter($mockPresenter)
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


        $discussionObj = new \Tymy\Discussion(NULL, TRUE, 1);
        $discussionObj
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

        $discussionObj = new \Tymy\Discussion($mockPresenter, TRUE, 1);
        $discussionObj->recId(1)
                ->fetch();
        Assert::true(is_object($discussionObj));
        Assert::true(is_object($discussionObj->result));
        Assert::type("string",$discussionObj->result->status);
        Assert::same("OK",$discussionObj->result->status);
        Assert::true(is_object($discussionObj->result->data->discussion));//returned discussion object
        Assert::type("int",$discussionObj->result->data->discussion->id);
    }

}

$test = new APIDiscussionTest($container);
$test->run();
