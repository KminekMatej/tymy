<?php

namespace Test\Tapi;

use Nette;
use Nette\Application\Request;
use Tester\Assert;
use Tester\Environment;

$container = require substr(__DIR__, 0, strpos(__DIR__, "tests/tapi")) . "tests/bootstrap.php";

if (in_array(basename(__FILE__, '.phpt') , $GLOBALS["testedTeam"]["skips"])) {
    Environment::skip('Test skipped as set in config file.');
}

class EventListResourceTest extends TapiTest {
    
    public function getCacheable() {
        return TRUE;
    }

    public function getJSONEncoding() {
        return TRUE;
    }

    public function getMethod() {
        return \Tapi\RequestMethod::GET;
    }

    public function setCorrectInputParams() {
        // nothing to set
    }
    
    public function testToRunAsFirst(){
        parent::primaryTests();
    }

    public function testPerformSuccess() {
        $data = parent::getPerformSuccessData();
        
        Assert::type("array", $data);//returned events array
        
        foreach ($data as $ev) {
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

    public function testFilter(){
        $data = $this->tapiObject->init()->setFrom("20160202")->getData(TRUE);
        Assert::type("array", $data);//returned events array
        Assert::count(1, $this->tapiObject->getRequestParameters());
        Assert::contains("filter",array_keys($this->tapiObject->getRequestParameters()));
        Assert::contains("startTime>20160202",$this->tapiObject->getRequestParameters());
        
        $data = $this->tapiObject->init()->setTo("20170202")->getData(TRUE);
        Assert::type("array", $data);//returned events array
        Assert::count(1, $this->tapiObject->getRequestParameters());
        Assert::contains("filter",array_keys($this->tapiObject->getRequestParameters()));
        Assert::contains("startTime<20170202",$this->tapiObject->getRequestParameters()["filter"]);
        
        $data = $this->tapiObject->init()->setFrom("20160202")->setTo("20170202")->getData(TRUE);
        Assert::type("array", $data);//returned events array
        Assert::count(1, $this->tapiObject->getRequestParameters());
        Assert::contains("filter",array_keys($this->tapiObject->getRequestParameters()));
        Assert::contains("startTime>20160202~startTime<20170202",$this->tapiObject->getRequestParameters()["filter"]);
        
        foreach ($data as $ev) {
            Assert::true(is_object($ev));
            Assert::true(property_exists($ev, "myAttendance"));
            Assert::type("string",$ev->myAttendance->preStatus);
            Assert::type("string",$ev->myAttendance->preDescription);
            Assert::type("string",$ev->myAttendance->postStatus);
            Assert::type("string",$ev->myAttendance->postDescription);
        }
        
        Assert::type("array", $this->tapiObject->getAsArray());
        
        foreach ($this->tapiObject->getAsArray() as $ev) {
            Assert::type("int",$ev->id);
            Assert::type("string",$ev->title);
            Assert::type("string",$ev->start);
            Assert::type("string",$ev->end);
            Assert::type("string",$ev->webName);
            
        }
        
        Assert::type("array", $this->tapiObject->getAsMonthArray());
        foreach ($this->tapiObject->getAsMonthArray() as $monthStr => $month) {
            Assert::type("array", $month);
            Assert::same(1, preg_match_all("/\d{4}-\d{2}/", $monthStr)); //timezone correction check
        }
        
        Assert::type("int", $this->tapiObject->getAllEventsCount());
    }
    
    function testSelectLimit() {
        $limit = 2;
        $data = $this->tapiObject->init()->setLimit($limit)->getData(TRUE);
        Assert::same($limit, count($data));
    }
    
    function testSelectLimitOffset() {
        $offset = 10;
        $limit = 5;
        Assert::count($limit, $this->tapiObject->init()->setOffset($offset)->setLimit($limit)->getData());
    }
    
}

$test = new EventListResourceTest($container);
$test->run();
