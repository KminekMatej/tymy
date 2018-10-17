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
        }, "\Tapi\Exception\APIException", "Poll ID is missing");
        Assert::exception(function() {
            $this->tapiObject->init()->setId($GLOBALS["testedTeam"]["testPollId"])->getData(TRUE);
        }, "\Tapi\Exception\APIException", "Poll option object is missing");
        Assert::exception(function() {
            $mockOptions = [
                ["caption" => "optionText"]
            ];
            $this->tapiObject->init()->setId($GLOBALS["testedTeam"]["testPollId"])->setPollOptions($mockOptions)->getData(TRUE);
        }, "\Tapi\Exception\APIException", "Option type is missing");
        Assert::exception(function() {
            $mockOptions = [
                ["type" => "BOOLEAN"]
            ];
            $this->tapiObject->init()->setId($GLOBALS["testedTeam"]["testPollId"])->setPollOptions($mockOptions)->getData(TRUE);
        }, "\Tapi\Exception\APIException", "Option caption is missing");
        
    }

    public function testPerformSuccess() {
        $this->authenticateTapi($GLOBALS["testedTeam"]["user_admin"], $GLOBALS["testedTeam"]["pass_admin"]);
        $this->tapiObject->init();
        $this->setCorrectInputParams();
        $data = $this->tapiObject->getData(TRUE);
        //edit
        $editor = $this->container->getByType("Tapi\OptionEditResource");
        foreach ($data as $opt) {
            $editor->init()->setId($opt->pollId)->setOption(["id" => $opt->id,"caption" => "editedOptionToText", "type" => "TEXT"])->perform();
        }
        
        //delete
        $deleter = $this->container->getByType("Tapi\OptionDeleteResource");
        foreach ($data as $opt) {
            $deleter->init()->setId($opt->pollId)->setOptionId($opt->id)->perform();
        }
        
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
