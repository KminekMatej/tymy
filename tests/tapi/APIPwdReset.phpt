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

class APIPwdReset extends ITapiTest {

    /** @var \Tymy\PwdReset */
    private $pwdReset;

    function __construct(Nette\DI\Container $container) {
        $this->container = $container;
    }
    
    public function getTestedObject() {
        return $this->pwdReset;
    }
    
    protected function setUp() {
        $this->pwdReset = $this->container->getByType('Tymy\PwdReset');
        parent::setUp();
    }
    
    /* TEST GETTERS AND SETTERS */ 
    
    function testCode(){
        $value = "103f5aabf99f879ecc3e";
        $this->pwdReset->setCode($value);
        Assert::equal($this->pwdReset->getCode(), $value);
    }
    
    /* TEST TAPI FUNCTIONS */ 
    
    /* TAPI : SELECT */
    
    function testSelectFailsNoCode(){
        $this->userTestAuthenticate("TESTLOGIN", "TESTPASS");
        Assert::exception(function(){$this->pwdReset->reset()->getResult();} , "\Tymy\Exception\APIException", "Code not set!");
    }
    
    function testSelectSuccessFailsNoCode(){
        Assert::exception(function(){$this->pwdReset->reset()->setCode("abc123test")->getResult();} , "\Tymy\Exception\APIException", "Not found");
        
    }
}

$test = new APIPwdReset($container);
$test->run();
