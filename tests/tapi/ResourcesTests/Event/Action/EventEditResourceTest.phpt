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

class EventEditResourceTest extends TapiTest {
    
    public function getCacheable() {
        return FALSE;
    }

    public function getJSONEncoding() {
        return TRUE;
    }

    public function getMethod() {
        return \Tapi\RequestMethod::PUT;
    }

    public function setCorrectInputParams() {
        $eventMock = [
            "caption" => "Autotest edited event",
        ];
        $this->tapiObject->setId($GLOBALS["testedTeam"]["testEventId"])->setEvent((object)$eventMock);
    }
    
    public function testErrors(){
        Assert::exception(function(){$this->tapiObject->init()->getData(TRUE);} , "\Tapi\Exception\APIException", "Event ID is missing");
        Assert::exception(function(){$this->tapiObject->init()->setId($GLOBALS["testedTeam"]["testEventId"])->getData(TRUE);} , "\Tapi\Exception\APIException", "Event object is missing");
    }

    public function testPerformSuccess() {
        //operational tests are performed on mother object (CRUD collaboration)
    }

}

$test = new EventEditResourceTest($container);
$test->run();
