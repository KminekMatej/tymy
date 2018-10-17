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

class PollVoteResourceTest extends TapiTest {
    
    public function getCacheable() {
        return FALSE;
    }

    public function getJSONEncoding() {
        return TRUE;
    }

    public function getMethod() {
        return \Tapi\RequestMethod::POST;
    }
    
    public function setCorrectInputParams() {
        $this->tapiObject->setId($GLOBALS["testedTeam"]["testPollId"])->setVotes($this->mockVotes());
    }
    
    public function testErrors() {
        Assert::exception(function() {
            $this->tapiObject->init()->getData(TRUE);
        }, "\Tapi\Exception\APIException", "Poll ID is missing");
        Assert::exception(function() {
            $this->tapiObject->init()->setId($GLOBALS["testedTeam"]["testPollId"])->getData(TRUE);
        }, "\Tapi\Exception\APIException", "Poll votes object is missing");
    }

    public function testPerformSuccess() {
        //operational tests are performed on mother object (CRUD collaboration)
    }
    
    private function mockVotes(){
        return [
            ["userId" => $this->user->getId(), "optionId" => 5, "stringValue" => md5("xxx" . rand(0, 1000))],
            ["userId" => $this->user->getId(), "optionId" => 8, "numericValue" => ""]
        ];
    }
        
    

}

$test = new PollVoteResourceTest($container);
$test->run();
