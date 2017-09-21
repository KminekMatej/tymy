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

class APIEventsTest extends ITapiTest {

    /** @var \Tymy\Events */
    private $events;

    function __construct(Nette\DI\Container $container) {
        $this->container = $container;
    }
    
    public function getTestedObject() {
        return $this->events;
    }
    
    protected function setUp() {
        $this->events = $this->container->getByType('Tymy\Events');
        parent::setUp();
    }
    
    function testFrom(){
        $from = "20160202";
        $this->events->reset()->setFrom($from);
        Assert::equal($from, $this->events->getFrom());
    }
    
    function testTo(){
        $to = "20170301";
        $this->events->reset()->setTo($to);
        Assert::equal($to, $this->events->getTo());
    }
    
    function testOrder(){
        $order = "startTime";
        $this->events->reset()->setOrder($order);
        Assert::equal($order, $this->events->getOrder());
    }
    
    function testLimit(){
        $limit = 2;
        $this->events->reset()->setLimit($limit);
        Assert::equal($limit, $this->events->getLimit());
    }
    
    function testOffset(){
        $offset = 15;
        $this->events->reset()->setOffset($offset);
        Assert::equal($offset, $this->events->getOffset());
    }
    
    /* TEST TAPI FUNCTIONS */ 
    
    /* TAPI : SELECT */

    function testSelectNotLoggedInFails404() {
        $this->userTestAuthenticate("TESTLOGIN", "TESTPASS");
        Assert::exception(function(){$this->events->reset()->getResult(TRUE);} , "\Tymy\Exception\APIException", "Login failed. Wrong username or password.");
    }
        
    function testSelectSuccess() {
        $this->userTapiAuthenticate($GLOBALS["testedTeam"]["user"], $GLOBALS["testedTeam"]["pass"]);
        
        $this->events->reset()->getResult(TRUE);

        Assert::same(1, count($this->events->getUriParams()));
        
        Assert::true(is_object($this->events));
        Assert::true(is_object($this->events->result));
        Assert::type("string",$this->events->result->status);
        Assert::same("OK",$this->events->result->status);
        
        Assert::type("array",$this->events->result->data);
        
        foreach ($this->events->result->data as $ev) {
            Assert::true(is_object($ev));
            Assert::type("int",$ev->id);
            Assert::type("string",$ev->caption);
            Assert::type("string",$ev->type);
            Assert::type("string",$ev->description);
            Assert::type("string",$ev->closeTime);
            Assert::type("string",$ev->startTime);
            Assert::type("string",$ev->endTime);
            Assert::type("string",$ev->link);
            Assert::type("string",$ev->place);
            Assert::type("bool",$ev->canView);
            Assert::type("bool",$ev->canPlan);
            Assert::type("bool",$ev->canResult);
            Assert::type("bool",$ev->inPast);
            Assert::type("bool",$ev->inFuture);
            Assert::true(!property_exists($ev, "myAttendance"));
        }
    }
    
    function testSelectWithMyAttendanceSuccess() {
        $this->userTapiAuthenticate($GLOBALS["testedTeam"]["user"], $GLOBALS["testedTeam"]["pass"]);
        $this->events->reset()->setWithMyAttendance(TRUE)->getResult(TRUE);
        
        Assert::same(1, count($this->events->getUriParams()));
        
        Assert::true(is_object($this->events));
        Assert::true(is_object($this->events->result));
        Assert::type("string",$this->events->result->status);
        Assert::same("OK",$this->events->result->status);
        
        Assert::type("array",$this->events->result->data);
        
        foreach ($this->events->result->data as $ev) {
            Assert::true(is_object($ev));
            Assert::type("int",$ev->id);
            Assert::type("string",$ev->caption);
            Assert::type("string",$ev->type);
            Assert::type("string",$ev->description);
            Assert::type("string",$ev->closeTime);
            Assert::type("string",$ev->startTime);
            Assert::type("string",$ev->endTime);
            Assert::type("string",$ev->link);
            Assert::type("string",$ev->place);
            Assert::type("bool",$ev->canView);
            Assert::type("bool",$ev->canPlan);
            Assert::type("bool",$ev->canResult);
            Assert::type("bool",$ev->inPast);
            Assert::type("bool",$ev->inFuture);
            Assert::true(property_exists($ev, "myAttendance"));
        }
    }
    
    function testSelectFilter() {
        $this->userTapiAuthenticate($GLOBALS["testedTeam"]["user"], $GLOBALS["testedTeam"]["pass"]);
        
        $this->events->reset()->getResult(TRUE);
        Assert::same(1, count($this->events->getUriParams()));
        Assert::contains("TSID",array_keys($this->events->getUriParams()));
        
        
        $this->events->reset()->setFrom("20160202")->getResult(TRUE);
        Assert::same(2, count($this->events->getUriParams()));
        Assert::contains("filter",array_keys($this->events->getUriParams()));
        Assert::contains("TSID",array_keys($this->events->getUriParams()));
        Assert::contains("startTime>20160202",$this->events->getUriParams());
        
        $this->events->reset()->setTo("20170202")->getResult(TRUE);
        Assert::same(2, count($this->events->getUriParams()));
        Assert::contains("filter",array_keys($this->events->getUriParams()));
        Assert::contains("TSID",array_keys($this->events->getUriParams()));
        Assert::contains("startTime<20170202",$this->events->getUriParams()["filter"]);
        
        $this->events->reset()->setFrom("20160202")->setTo("20170202")->getResult(TRUE);
        
        Assert::same(2, count($this->events->getUriParams()));
        Assert::contains("filter",array_keys($this->events->getUriParams()));
        Assert::contains("TSID",array_keys($this->events->getUriParams()));
        Assert::contains("startTime>20160202~startTime<20170202",$this->events->getUriParams()["filter"]);
        
        $this->events->reset()->setFrom("20160202")->setTo("20170202")->setWithMyAttendance(TRUE)->getResult(TRUE);
        
        Assert::same(2, count($this->events->getUriParams()));
        Assert::contains("filter",array_keys($this->events->getUriParams()));
        Assert::contains("TSID",array_keys($this->events->getUriParams()));
        Assert::contains("startTime>20160202~startTime<20170202",$this->events->getUriParams()["filter"]);

        foreach ($this->events->result->data as $ev) {
            Assert::true(is_object($ev));
            Assert::true(property_exists($ev, "myAttendance"));
            Assert::type("string",$ev->myAttendance->preStatus);
            Assert::type("string",$ev->myAttendance->preDescription);
            Assert::type("string",$ev->myAttendance->postStatus);
            Assert::type("string",$ev->myAttendance->postDescription);
        }
    }

}

$test = new APIEventsTest($container);
$test->run();
