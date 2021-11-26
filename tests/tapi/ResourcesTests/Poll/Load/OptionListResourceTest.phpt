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

class OptionListResourceTest extends TapiTest {
    
    public function getCacheable() {
        return TRUE;
    }

    public function getJSONEncoding() {
        return TRUE;
    }

    public function getMethod() {
        return \Tymy\Module\Core\Model\RequestMethod::GET;
    }

    public function setCorrectInputParams() {
        $this->tapiObject->setId($GLOBALS["testedTeam"]["testPollId"]);
    }
    
    public function testErrorNoId(){
        Assert::exception(function(){$this->tapiObject->init()->getData(TRUE);} , "\Tapi\Exception\APIException", "Poll ID is missing");
    }

    public function testPerformSuccess() {
        $data = parent::getPerformSuccessData();
        
        Assert::type("array",$data);
        
        foreach ($data as $opt) {
            Assert::type("int",$opt->id);
            Assert::true($opt->id > 0);
            Assert::type("int",$opt->pollId);
            Assert::type("string",$opt->caption);
            Assert::type("string",$opt->type);
            Assert::true(in_array($opt->type, ["TEXT", "NUMBER", "BOOLEAN"]));
        }
        
    }
}

$test = new OptionListResourceTest($container);
$test->run();
