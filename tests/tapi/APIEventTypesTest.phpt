<?php
/**
 * TEST: Test Events on TYMY api
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

class APIEventTypesTest extends ITapiTest {

    /** @var \Tymy\EventTypes */
    private $eventTypes;

    function __construct(Nette\DI\Container $container) {
        $this->container = $container;
    }
    
    public function getTestedObject() {
        return $this->eventTypes;
    }
    
    protected function setUp() {
        $this->eventTypes = $this->container->getByType('Tymy\EventTypes');
        parent::setUp();
    }
    
    /* TEST GETTERS AND SETTERS */ 
    
    /* TEST TAPI FUNCTIONS */ 
    
    /* TAPI : SELECT */

    function testFetchNotLoggedInFails404() {
        $this->userTestAuthenticate("TESTLOGIN", "TESTPASS");
        Assert::exception(function(){$this->eventTypes->getResult(TRUE);} , "\Tymy\Exception\APIException", "Login failed. Wrong username or password.");
    }
        
    function testSelectSuccess() {
        $this->userTapiAuthenticate($GLOBALS["testedTeam"]["user"], $GLOBALS["testedTeam"]["pass"]);
        $this->eventTypes->getResult(TRUE);
        
        Assert::same(1, count($this->eventTypes->getUriParams()));
        
        Assert::true(is_object($this->eventTypes));
        Assert::true(is_object($this->eventTypes->result));
        Assert::type("string",$this->eventTypes->result->status);
        Assert::same("OK",$this->eventTypes->result->status);
        
        Assert::type("array",$this->eventTypes->result->data);
        
        foreach ($this->eventTypes->result->data as $key => $evt) {
            Assert::equal($key, $evt->code); //check that key equals evt->code
            Assert::true(is_object($evt));
            Assert::type("int",$evt->id);
            Assert::true($evt->id > 0);
            Assert::type("string",$evt->code);
            Assert::type("string",$evt->caption);
            Assert::type("int",$evt->preStatusSetId);
            Assert::true($evt->preStatusSetId > 0);
            Assert::type("int",$evt->postStatusSetId);
            Assert::true($evt->postStatusSetId > 0);
            Assert::type("array",$evt->preStatusSet);
            foreach ($evt->preStatusSet as $key_pre => $evtSS) {
                Assert::equal($key_pre, $evtSS->code); //check that key equals code
                Assert::type("int",$evtSS->id);
                Assert::true($evtSS->id > 0);
                Assert::type("string",$evtSS->code);
                Assert::type("string",$evtSS->caption);
            }
            Assert::type("array",$evt->postStatusSet);
            foreach ($evt->postStatusSet as $key_post => $evtSS) {
                Assert::equal($key_post, $evtSS->code); //check that key equals code
                Assert::type("int",$evtSS->id);
                Assert::true($evtSS->id > 0);
                Assert::type("string",$evtSS->code);
                Assert::type("string",$evtSS->caption);
            }
        }
    }
}

$test = new APIEventTypesTest($container);
$test->run();
