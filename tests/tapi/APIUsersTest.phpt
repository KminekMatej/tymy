<?php
/**
 * TEST: Test Users on TYMY api
 * 
 */

namespace Test;

use Nette;
use Tester;
use Tester\Assert;

$container = require __DIR__ . '/../bootstrap.php';
Tester\Environment::skip('Temporary skipping');
if (in_array(basename(__FILE__, '.phpt') , $GLOBALS["testedTeam"]["skips"])) {
    Tester\Environment::skip('Test skipped as set in config file.');
}

class APIUsersTest extends ITapiTest{

    private $container;
    private $authenticator;

    function __construct(Nette\DI\Container $container) {
        $this->container = $container;
    }
    
    function setUp() {
        parent::setUp();
        $this->initTapiConfiguration($this->container);
        $this->authenticator = new \App\Model\TestAuthenticator();
    }
    
    function tearDown() {
        parent::tearDown();
    }

    /**
     * @throws Nette\Application\AbortException
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


        $usersObj = new \Tymy\Users();
        $usersObj->setPresenter($mockPresenter)
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
        $this->authenticator->setArr(["sessionKey" => "dsfbglsdfbg13546"]);

        $mockPresenter->getUser()->setAuthenticator($this->authenticator);
        $mockPresenter->getUser()->login("test", "test");


        $usersObj = new \Tymy\Users();
        $usersObj->setPresenter($mockPresenter)
                ->fetch();
    }
    
    function testFetchSuccessAll() {
        $presenterFactory = $this->container->getByType('Nette\Application\IPresenterFactory');
        $mockPresenter = $presenterFactory->createPresenter('Discussion');
        $mockPresenter->autoCanonicalize = FALSE;

        $this->login();
        $this->authenticator->setId($this->login->id);
        $this->authenticator->setArr(["sessionKey" => $this->loginObj->getResult()->sessionKey]);
        $mockPresenter->getUser()->setAuthenticator($this->authenticator);
        $mockPresenter->getUser()->setExpiration('2 minutes');
        $mockPresenter->getUser()->login($GLOBALS["testedTeam"]["user"], $GLOBALS["testedTeam"]["pass"]);

        $usersObj = new \Tymy\Users($mockPresenter->tapiAuthenticator, $mockPresenter);
        $usersObj->fetch();
        
        Assert::same(1, count($usersObj->getUriParams()));
        
        Assert::true(is_object($usersObj));
        Assert::true(is_object($usersObj->result));
        Assert::type("string",$usersObj->result->status);
        Assert::same("OK",$usersObj->result->status);
        
        Assert::type("array",$usersObj->result->data);
        
        foreach ($usersObj->result->data as $u) {
            Assert::true(is_object($u));
            Assert::type("int",$u->id);
            Assert::type("string",$u->login);
            Assert::type("bool",$u->canLogin);
            Assert::type("string",$u->lastLogin);
            Assert::same(1, preg_match_all($GLOBALS["dateRegex"], $u->lastLogin)); //timezone correction check
            Assert::type("string",$u->status);
            Assert::true(in_array($u->status, ["PLAYER", "MEMBER", "SICK", "DELETED", "INIT"]));

            Assert::type("string",$u->firstName);
            Assert::type("string",$u->lastName);
            Assert::type("string",$u->callName);
            Assert::type("string",$u->language);
            //Assert::type("string",$u->email);
            Assert::type("string",$u->jerseyNumber);
            //Assert::type("string",$u->gender);
            //Assert::type("string",$u->street);
            //Assert::type("string",$u->city);
            //Assert::type("string",$u->zipCode);
            //Assert::type("string",$u->phone);
            //Assert::type("string",$u->phone2);
            //Assert::type("string",$u->birthDate);
            Assert::type("int",$u->nameDayMonth);
            Assert::type("int",$u->nameDayDay);
            Assert::type("string",$u->pictureUrl);
            Assert::type("string",$u->fullName);
            Assert::type("string",$u->displayName);
            Assert::type("string",$u->webName);
            Assert::type("int",$u->errCnt);
            Assert::type("array",$u->errFls);
            foreach ($u->errFls as $errF) {
                Assert::type("string",$errF);
            }
            
        }
    }
    
    function getUserGoodTypes() {
        return [
            ["MEMBER"],
            ["PLAYER"],
            ["SICK"],
            ["PLAYER"]
        ];
    }

    function getUserBadTypes() {
        return [
            ["35163531"],
            ["adwskfnlx"],
        ];
    }

    /**
     * 
     * @dataProvider getUserGoodTypes
     */
    function testFetchSuccessTypes($userType) {
        $presenterFactory = $this->container->getByType('Nette\Application\IPresenterFactory');
        $mockPresenter = $presenterFactory->createPresenter('Team');
        $mockPresenter->autoCanonicalize = FALSE;

        $this->login();
        $this->authenticator->setId($this->login->id);
        $this->authenticator->setArr(["sessionKey" => $this->loginObj->getResult()->sessionKey]);
        $mockPresenter->getUser()->setAuthenticator($this->authenticator);
        $mockPresenter->getUser()->setExpiration('2 minutes');
        $mockPresenter->getUser()->login($GLOBALS["testedTeam"]["user"], $GLOBALS["testedTeam"]["pass"]);
        
        $usersObj = new \Tymy\Users($mockPresenter->tapiAuthenticator, $mockPresenter, $userType);
        $usersObj->fetch();
        
        foreach ($usersObj->result->data as $data) {
            Assert::type("string", $data->status);
            Assert::same($userType, $data->status);
        }
    }
    
    /**
     * @dataProvider getUserBadTypes
     */
    function testFetchFailsTypes($userType) {
        $presenterFactory = $this->container->getByType('Nette\Application\IPresenterFactory');
        $mockPresenter = $presenterFactory->createPresenter('Team');
        $mockPresenter->autoCanonicalize = FALSE;

        $this->login();
        $this->authenticator->setId($this->login->id);
        $this->authenticator->setArr(["sessionKey" => $this->loginObj->getResult()->sessionKey]);
        $mockPresenter->getUser()->setAuthenticator($this->authenticator);
        $mockPresenter->getUser()->setExpiration('2 minutes');
        $mockPresenter->getUser()->login($GLOBALS["testedTeam"]["user"], $GLOBALS["testedTeam"]["pass"]);
        
        $usersObj = new \Tymy\Users($mockPresenter->tapiAuthenticator, $mockPresenter, $userType);
        $usersObj->fetch();
        
        Assert::type("array", $usersObj->result->data);
        Assert::same(0, count($usersObj->result->data));
    }
}

$test = new APIUsersTest($container);
$test->run();
