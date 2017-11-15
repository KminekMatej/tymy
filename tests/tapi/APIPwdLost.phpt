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

class APIPwdLost extends ITapiTest {

    /** @var \Tymy\PwdLost */
    private $pwdLost;

    function __construct(Nette\DI\Container $container) {
        $this->container = $container;
    }
    
    public function getTestedObject() {
        return $this->pwdLost;
    }
    
    protected function setUp() {
        $this->pwdLost = $this->container->getByType('Tymy\PwdLost');
        parent::setUp();
    }
    
    /* TEST GETTERS AND SETTERS */
    
    function testMail(){
        $value = "test@test.com";
        $this->pwdLost->setMail($value);
        Assert::equal($this->pwdLost->getMail(), $value);
    }
    
    function testHostname(){
        $value = "autotest_hostname";
        $this->pwdLost->setHostname($value);
        Assert::equal($this->pwdLost->getHostname(), $value);
    }
    
    function testCallback(){
        $value = "http://www.autotest.tymy.cz/?code=%s";
        $this->pwdLost->setCallbackUri($value);
        Assert::equal($this->pwdLost->getCallbackUri(), $value);
    }
    
    /* TEST TAPI FUNCTIONS */ 
    
    /* TAPI : SELECT */
    
    function testSelectFailsNoMail() {
        $this->userTestAuthenticate("TESTLOGIN", "TESTPASS");
        Assert::exception(function(){$this->pwdLost->reset()->getResult();} , "\Tymy\Exception\APIException", "E-mail not set!");
    }

    function testSelectFailsNoHostname() {
        $this->userTestAuthenticate("TESTLOGIN", "TESTPASS");
        Assert::exception(function(){$this->pwdLost->reset()->setMail($GLOBALS["testedTeam"]["userMail"])->getResult();} , "\Tymy\Exception\APIException", "Hostname not set!");
    }

    function testSelectFailsNoCallback() {
        $this->userTestAuthenticate("TESTLOGIN", "TESTPASS");
        Assert::exception(function(){$this->pwdLost->reset()->setMail($GLOBALS["testedTeam"]["userMail"])->setHostname("autotester_host")->getResult();} , "\Tymy\Exception\APIException", "Callback not set!");
    }
    
    function testSelectSuccess(){
        if(!$GLOBALS["testedTeam"]["invasive"])
            return null;
        $this->userTapiAuthenticate($GLOBALS["testedTeam"]["user"], $GLOBALS["testedTeam"]["pass"]);
        $hostname = "autotester_host";
        $callback = "http://www.autotest.tymy.cz/?code=%s";
        $this->pwdLost->reset()->setMail($GLOBALS["testedTeam"]["userMail"])->setHostname($hostname)->setCallbackUri($callback)->getResult();
        
        Assert::true(is_object($this->pwdLost->result));
        Assert::type("string",$this->pwdLost->result->status);
        Assert::same("OK",$this->pwdLost->result->status);
    }
    
    function testResetWorks(){
        $this->pwdLost->setMail("kajlsdf")->setHostname("temphost")->setCallbackUri("callnowhere");
        $this->pwdLost->reset();
        Assert::null($this->pwdLost->getMail());
        Assert::null($this->pwdLost->getHostname());
        Assert::null($this->pwdLost->getCallbackUri());
        parent::resetParentTest($this->pwdLost);
    }
}

$test = new APIPwdLost($container);
$test->run();
