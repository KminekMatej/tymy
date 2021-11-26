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

class OptionEditResourceTest extends TapiTest {
    
    public function getCacheable() {
        return FALSE;
    }

    public function getJSONEncoding() {
        return TRUE;
    }

    public function getMethod() {
        return \Tymy\Module\Core\Model\RequestMethod::PUT;
    }
    
    public function setCorrectInputParams() {
        $this->tapiObject->setId($GLOBALS["testedTeam"]["testPollId"])->setOption($this->mockPollOption());
    }
    
    public function testErrors() {
        Assert::exception(function() {
            $this->tapiObject->init()->getData(TRUE);
        }, "\Tapi\Exception\APIException", "Poll ID is missing");
        Assert::exception(function() {
            $this->tapiObject->init()->setId($GLOBALS["testedTeam"]["testPollId"])->getData(TRUE);
        }, "\Tapi\Exception\APIException", "Option object is missing");
    }

    public function testPerformSuccess() {
        //operational tests are performed on mother object (CRUD collaboration)
    }

    private function mockPollOption(){
        return ["id" => $GLOBALS["testedTeam"]["testPollOptionId"], "caption" => "optionText", "type" => "TEXT"];
    }
}

$test = new OptionEditResourceTest($container);
$test->run();
