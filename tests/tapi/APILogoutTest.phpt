<?php
/**
 * TEST: Test Logout on TYMY api
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

class APILogoutTest extends ITapiTest {

    /** @var \Tymy\Logout */
    private $logout;

    function __construct(Nette\DI\Container $container) {
        $this->container = $container;
    }
    
    public function getTestedObject() {
        return $this->logout;
    }
    
    protected function setUp() {
        $this->logout = $this->container->getByType('Tymy\Logout');
        parent::setUp();
    }
    
    /* TEST GETTERS AND SETTERS */ 
    
    /* TEST TAPI FUNCTIONS */ 
    
    /* TAPI : SELECT */

    /**
     * @throws \Tymy\Exception\APIException
     */
    function testFetchLogoutNotLoggedInFails500() {
        $presenterFactory = $this->container->getByType('Nette\Application\IPresenterFactory');
        $mockPresenter = $presenterFactory->createPresenter('Homepage');
        $mockPresenter->autoCanonicalize = FALSE;

        $this->authenticator->setId(38);
        $this->authenticator->setStatus(["TESTROLE", "TESTROLE2"]);
        $this->authenticator->setArr(["sessionKey" => "dsfbglsdfbg13546"]);

        $mockPresenter->getUser()->setAuthenticator($this->authenticator);
        $mockPresenter->getUser()->login("test", "test");

        $logoutObj = new \Tymy\Logout(NULL);
        $logoutObj->setPresenter($mockPresenter)
                ->logout();
    }
    
    function testLogoutSuccess() {
        $presenterFactory = $this->container->getByType('Nette\Application\IPresenterFactory');
        $mockPresenter = $presenterFactory->createPresenter('Discussion');
        $mockPresenter->autoCanonicalize = FALSE;

        $this->login();
        $this->authenticator->setId($this->login->id);
        $this->authenticator->setArr(["sessionKey" => $this->loginObj->getResult()->sessionKey]);
        $mockPresenter->getUser()->setAuthenticator($this->authenticator);
        $mockPresenter->getUser()->setExpiration('2 minutes');
        $mockPresenter->getUser()->login($GLOBALS["testedTeam"]["user"], $GLOBALS["testedTeam"]["pass"]);

        $logoutObj = new \Tymy\Logout($mockPresenter->tapiAuthenticator, $mockPresenter);
        $logoutObj->logout();
        
        Assert::same(1, count($logoutObj->getUriParams()));
        
        Assert::true(is_object($logoutObj));
        Assert::true(is_object($logoutObj->result));
        Assert::type("string",$logoutObj->result->status);
        Assert::same("OK",$logoutObj->result->status);

        Assert::true(!property_exists($logoutObj->result, "data"));
    }

}

$test = new APILogoutTest($container);
$test->run();
