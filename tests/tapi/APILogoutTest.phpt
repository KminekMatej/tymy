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

if (in_array(basename(__FILE__, '.phpt') , $GLOBALS["testedTeam"]["skips"])) {
    Tester\Environment::skip('Test skipped as set in config file.');
}

class APILogoutTest extends ITapiTest {

    /** @var \Tymy\Logout */
    private $logout;
    
    /** @var \Tymy\Login */
    private $login;

    function __construct(Nette\DI\Container $container) {
        $this->container = $container;
    }
    
    public function getTestedObject() {
        return $this->logout;
    }
    
    protected function setUp() {
        $this->logout = $this->container->getByType('Tymy\Logout');
        parent::setUp();
        $this->login = new \Tymy\Login($this->supplier);
    }
    
    /* TEST GETTERS AND SETTERS */ 
    
    /* TEST TAPI FUNCTIONS */ 
    
    /* TAPI : SELECT */

    function testLogoutNotLoggedInFails401() {
        $this->userTestAuthenticate("TESTLOGIN", "TESTPASS");
        Assert::exception(function(){$this->logout->logout();} , "\Tymy\Exception\APIException", "Login failed. Wrong username or password.");
    }
    
    function testLogoutSuccess() {
        $this->userTapiAuthenticate($GLOBALS["testedTeam"]["user"], $GLOBALS["testedTeam"]["pass"]);
        $this->logout->logout();
        
        Assert::same(1, count($this->logout->getUriParams()));
        
        Assert::true(is_object($this->logout));
        Assert::true(is_object($this->logout->result));
        Assert::type("string",$this->logout->result->status);
        Assert::same("OK",$this->logout->result->status);

        Assert::true(!property_exists($this->logout->result, "data"));
        $session = $this->container->getByType('Nette\Http\Session');
        Assert::null($session->getSection("TAPI"));
    }

}

$test = new APILogoutTest($container);
$test->run();
