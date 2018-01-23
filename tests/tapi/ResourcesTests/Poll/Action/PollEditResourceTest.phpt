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

class PollEditResourceTest extends TapiTest {
    
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
        $this->tapiObject->setId($GLOBALS["testedTeam"]["testPollId"])->setPoll($this->mockPoll());
    }
    
    public function testErrors() {
        Assert::exception(function() {
            $this->tapiObject->init()->getData(TRUE);
        }, "\Tapi\Exception\APIException", "Poll ID not set");
        Assert::exception(function() {
            $this->tapiObject->init()->setId($GLOBALS["testedTeam"]["testPollId"])->getData(TRUE);
        }, "\Tapi\Exception\APIException", "Poll object not set");
    }

    public function testPerformSuccess() {
        //operational tests are performed on mother object (CRUD collaboration)
    }
    
    private function mockPoll(){
        return ["caption"=>"Autotest poll " . rand(0, 100)];
    }

}

$test = new PollEditResourceTest($container);
$test->run();
