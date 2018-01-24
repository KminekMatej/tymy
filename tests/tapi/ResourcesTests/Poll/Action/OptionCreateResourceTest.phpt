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

class OptionCreateResourceTest extends TapiTest {
    
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
        $this->tapiObject->setId($GLOBALS["testedTeam"]["testPollId"])->setPollOptions($this->mockPollOptions());
    }
    
    public function testErrors() {
        Assert::exception(function() {
            $this->tapiObject->init()->getData(TRUE);
        }, "\Tapi\Exception\APIException", "Poll ID not set");
        Assert::exception(function() {
            $this->tapiObject->init()->setId($GLOBALS["testedTeam"]["testPollId"])->getData(TRUE);
        }, "\Tapi\Exception\APIException", "Poll option object not set");
        Assert::exception(function() {
            $mockOptions = [
                ["caption" => "optionText"]
            ];
            $this->tapiObject->init()->setId($GLOBALS["testedTeam"]["testPollId"])->setPollOptions($mockOptions)->getData(TRUE);
        }, "\Tapi\Exception\APIException", "Option type not set");
        Assert::exception(function() {
            $mockOptions = [
                ["type" => "BOOLEAN"]
            ];
            $this->tapiObject->init()->setId($GLOBALS["testedTeam"]["testPollId"])->setPollOptions($mockOptions)->getData(TRUE);
        }, "\Tapi\Exception\APIException", "Option caption not set");
        
    }

    public function testPerformSuccess() {
        $data = parent::getPerformSuccessData();
        
        $userDeleter = $this->container->getByType("Tapi\OptionDeleteResource");
        $userDeleter->setId($data->id)->perform();
    }
    
    private function mockPollOptions(){
        return [
            ["caption" => "optionText", "type" => "TEXT"],
            ["caption" => "optionNum", "type" => "NUMBER"],
            ["caption" => "optionBool", "type" => "BOOLEAN"]
        ];
    }
}

$test = new OptionCreateResourceTest($container);
$test->run();
