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

class APIUserTest extends Tester\TestCase {

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
        $userObj = new \Tymy\User(NULL);
        $user = $userObj
                ->team("dev")
                ->fetch();
    }
    
    /**
     * @throws Tymy\Exception\APIException
     */
    function testFetchNotLoggedInFails404() {
        $presenterFactory = $this->container->getByType('Nette\Application\IPresenterFactory');
        $mockPresenter = $presenterFactory->createPresenter('Team');
        $mockPresenter->autoCanonicalize = FALSE;

        $this->authenticator->setId(38);
        $this->authenticator->setStatus(["TESTROLE", "TESTROLE2"]);
        $this->authenticator->setArr(["tym" => "testteam", "sessionKey" => "dsfbglsdfbg13546"]);

        $mockPresenter->getUser()->setAuthenticator($this->authenticator);
        $mockPresenter->getUser()->login("test", "test");


        $userObj = new \Tymy\User(NULL);
        $userObj
                ->presenter($mockPresenter)
                ->recId(1)
                ->fetch();
    }
    
    /**
     * @throws Nette\Application\AbortException
     */
    function testFetchNotLoggedInRedirects() {
        $presenterFactory = $this->container->getByType('Nette\Application\IPresenterFactory');
        $mockPresenter = $presenterFactory->createPresenter('Team');
        $mockPresenter->autoCanonicalize = FALSE;

        $this->authenticator->setId(38);
        $this->authenticator->setStatus(["TESTROLE", "TESTROLE2"]);
        $this->authenticator->setArr(["tym" => "dev", "sessionKey" => "dsfbglsdfbg13546"]);

        $mockPresenter->getUser()->setAuthenticator($this->authenticator);
        $mockPresenter->getUser()->login("test", "test");


        $userObj = new \Tymy\User(NULL);
        $userObj
                ->presenter($mockPresenter)
                ->recId(1)
                ->fetch();
    }
    
    function testFetchSuccess() {
        $presenterFactory = $this->container->getByType('Nette\Application\IPresenterFactory');
        $mockPresenter = $presenterFactory->createPresenter('Team');
        $mockPresenter->autoCanonicalize = FALSE;

        $this->login();
        $this->authenticator->setId($this->login->id);
        $this->authenticator->setArr(["tym" => "dev", "sessionKey" => $this->loginObj->getResult()->sessionKey]);
        $mockPresenter->getUser()->setAuthenticator($this->authenticator);
        $mockPresenter->getUser()->setExpiration('2 minutes');
        $mockPresenter->getUser()->login($GLOBALS["username"], $GLOBALS["password"]);

        $userObj = new \Tymy\User($mockPresenter);
        $userObj->recId(1)
                ->fetch();
        
        var_dump($userObj);
        Assert::true(is_object($userObj));
        Assert::true(is_object($userObj->result));
        Assert::type("string",$userObj->result->status);
        Assert::same("OK",$userObj->result->status);
        
        Assert::type("int",$userObj->result->data->id);
        Assert::type("string",$userObj->result->data->login);
        Assert::type("bool",$userObj->result->data->canLogin);
        Assert::type("string",$userObj->result->data->lastLogin);
        Assert::type("string",$userObj->result->data->status);
        Assert::type("array",$userObj->result->data->roles);
        foreach ($userObj->result->data->roles as $role) {
            Assert::type("string",$role);
        }
        
        Assert::type("string",$userObj->result->data->firstName);
        Assert::type("string",$userObj->result->data->lastName);
        Assert::type("string",$userObj->result->data->callName);
        Assert::type("string",$userObj->result->data->language);
        Assert::type("string",$userObj->result->data->email);
        Assert::type("string",$userObj->result->data->jerseyNumber);
        Assert::type("string",$userObj->result->data->gender);
        Assert::type("string",$userObj->result->data->street);
        Assert::type("string",$userObj->result->data->city);
        Assert::type("string",$userObj->result->data->zipCode);
        Assert::type("string",$userObj->result->data->phone);
        Assert::type("string",$userObj->result->data->phone2);
        Assert::type("string",$userObj->result->data->birthDate);
        Assert::type("int",$userObj->result->data->nameDayMonth);
        Assert::type("int",$userObj->result->data->nameDayDay);
        Assert::type("string",$userObj->result->data->pictureUrl);
        Assert::type("string",$userObj->result->data->fullName);
        Assert::type("string",$userObj->result->data->displayName);
        Assert::type("string",$userObj->result->data->webName);
        Assert::type("int",$userObj->result->data->errCnt);
        Assert::type("array",$userObj->result->data->errFls);
        foreach ($userObj->result->data->errFls as $errF) {
            Assert::type("string",$errF);
        }
        
    }

}

$test = new APIUserTest($container);
$test->run();
