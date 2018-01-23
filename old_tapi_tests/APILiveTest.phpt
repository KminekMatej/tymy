<?php
/**
 * TEST: Test Login on TYMY api
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

class APILiveTest extends ITapiTest {

    /** @var \Tymy\Live */
    private $live;

    function __construct(Nette\DI\Container $container) {
        $this->container = $container;
    }
    
    public function getTestedObject() {
        return $this->live;
    }
    
    protected function setUp() {
        $this->live = $this->container->getByType('Tymy\Live');
        parent::setUp();
    }
    
    /* TEST GETTERS AND SETTERS */ 
    
    /* TEST TAPI FUNCTIONS */ 
    
    /* TAPI : SELECT */
    
    function testSelectNotLoggedInFails404() {
        $this->userTestAuthenticate("TESTLOGIN", "TESTPASS");
        Assert::exception(function(){$this->live->reset()->getResult(TRUE);} , "\Tymy\Exception\APIException", "Login failed. Wrong username or password.");
    }
    
    function testSelectSuccess() {
        $this->userTapiAuthenticate($GLOBALS["testedTeam"]["user"], $GLOBALS["testedTeam"]["pass"]);
        $this->live->reset()->getResult(TRUE);
        
        Assert::same(1, count($this->live->getUriParams()));
        
        Assert::true(is_object($this->live));
        Assert::true(is_object($this->live->result));
        Assert::type("string",$this->live->result->status);
        Assert::same("OK",$this->live->result->status);
        
        Assert::type("array",$this->live->result->data);
        Assert::true(count($this->live->result->data) >= 1);
        
        foreach ($this->live->result->data as $u) {
            Assert::type("string",$u->webName); //its the only thing post processed in lives
        }
    }
}

$test = new APILiveTest($container);
$test->run();
