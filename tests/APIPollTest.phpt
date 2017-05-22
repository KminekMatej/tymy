<?php
/**
 * TEST: Test Poll detail on TYMY api
 * 
 */

namespace Test;

use Nette;
use Tester;
use Tester\Assert;

$container = require __DIR__ . '/bootstrap.php';

class APIPollTest extends Tester\TestCase {

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
    function testFetchNotLoggedInFailsRecIdNotSet() {
        $pollObj = new \Tymy\Poll(NULL);
        $pollObj->fetch();
    }
    
    /**
     * @throws Tymy\Exception\APIException
     */
    function testFetchNotLoggedInFails404() {
        $presenterFactory = $this->container->getByType('Nette\Application\IPresenterFactory');
        $mockPresenter = $presenterFactory->createPresenter('Poll');
        $mockPresenter->autoCanonicalize = FALSE;

        $this->authenticator->setId(38);
        $this->authenticator->setStatus(["TESTROLE", "TESTROLE2"]);
        $this->authenticator->setArr(["tym" => "testteam", "sessionKey" => "dsfbglsdfbg13546"]);

        $mockPresenter->getUser()->setAuthenticator($this->authenticator);
        $mockPresenter->getUser()->login("test", "test");

        $pollObj = new \Tymy\Poll(NULL);
        $pollObj->presenter($mockPresenter)
                ->recId(1)
                ->fetch();
    }
    
    /**
     * @throws Nette\Application\AbortException
     */
    function testFetchNotLoggedInRedirects() {
        $presenterFactory = $this->container->getByType('Nette\Application\IPresenterFactory');
        $mockPresenter = $presenterFactory->createPresenter('Poll');
        $mockPresenter->autoCanonicalize = FALSE;

        $this->authenticator->setId(38);
        $this->authenticator->setStatus(["TESTROLE", "TESTROLE2"]);
        $this->authenticator->setArr(["tym" => "dev", "sessionKey" => "dsfbglsdfbg13546"]);

        $mockPresenter->getUser()->setAuthenticator($this->authenticator);
        $mockPresenter->getUser()->login("test", "test");

        $pollObj = new \Tymy\Poll(NULL);
        $pollObj->presenter($mockPresenter)
                ->recId(1)
                ->fetch();
    }
    
    function testFetchSuccess() {
        $presenterFactory = $this->container->getByType('Nette\Application\IPresenterFactory');
        $mockPresenter = $presenterFactory->createPresenter('Poll');
        $mockPresenter->autoCanonicalize = FALSE;

        $this->login();
        $this->authenticator->setId($this->login->id);
        $this->authenticator->setArr(["tym" => "dev", "sessionKey" => $this->loginObj->getResult()->sessionKey]);
        $mockPresenter->getUser()->setAuthenticator($this->authenticator);
        $mockPresenter->getUser()->setExpiration('2 minutes');
        $mockPresenter->getUser()->login($GLOBALS["username"], $GLOBALS["password"]);

        $pollId = 1;
        $pollObj = new \Tymy\Poll($mockPresenter);
        $pollObj->recId($pollId)
                ->fetch();
        Assert::true(is_object($pollObj));
        Assert::true(is_object($pollObj->result));
        Assert::type("string",$pollObj->result->status);
        Assert::same("OK",$pollObj->result->status);
        
        Assert::type("int",$pollObj->result->data->id);
        Assert::same($pollId,$pollObj->result->data->id);
        
        Assert::type("int",$pollObj->result->data->createdById);
        Assert::type("string",$pollObj->result->data->createdAt);
        Assert::type("int",$pollObj->result->data->updatedById);
        Assert::type("string",$pollObj->result->data->updatedAt);
        Assert::type("string",$pollObj->result->data->caption);
        Assert::type("string",$pollObj->result->data->description);
        Assert::type("int",$pollObj->result->data->minItems);
        Assert::true($pollObj->result->data->minItems > 0 || $pollObj->result->data->minItems == -1);
        Assert::type("int",$pollObj->result->data->maxItems);
        Assert::true($pollObj->result->data->maxItems > 0 || $pollObj->result->data->maxItems == -1);
        Assert::true($pollObj->result->data->maxItems >= $pollObj->result->data->minItems);
        Assert::type("bool",$pollObj->result->data->changeableVotes);
        Assert::type("bool",$pollObj->result->data->mainMenu);
        Assert::type("bool",$pollObj->result->data->anonymousResults);
        Assert::type("string",$pollObj->result->data->showResults);
        Assert::true(in_array($pollObj->result->data->showResults, ["NEVER", "ALWAYS", "AFTER_VOTE", "WHEN_CLOSED"]));
        Assert::type("string",$pollObj->result->data->status);
        Assert::true(in_array($pollObj->result->data->status, ["DESIGN", "OPENED", "CLOSED"]));
        Assert::type("string",$pollObj->result->data->resultRightName);
        Assert::type("string",$pollObj->result->data->voteRightName);
        Assert::type("int",$pollObj->result->data->orderFlag);
        
        Assert::type("array",$pollObj->result->data->options);
        foreach ($pollObj->result->data->options as $opt) {
            Assert::type("int",$opt->id);
            Assert::true($opt->id > 0);
            Assert::type("int",$opt->pollId);
            Assert::same($pollId,$opt->pollId);
            Assert::type("string",$opt->caption);
            Assert::type("string",$opt->type);
            Assert::true(in_array($opt->type, ["TEXT", "NUMBER", "BOOLEAN"]));
        }
    }
}

$test = new APIPollTest($container);
$test->run();
