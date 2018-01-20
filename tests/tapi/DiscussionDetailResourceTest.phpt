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

class DiscussionDetailResourceTest extends TapiTest {
    
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
        $this->tapiObject->setId($GLOBALS["testedTeam"]["testDiscussionId"]);
    }
    
    public function testErrorNoId(){
        Assert::exception(function(){$this->tapiObject->init()->getData(TRUE);} , "\Tapi\Exception\APIException", "Discussion ID is missing");
    }

    public function testPerformSuccess() {
        $this->authenticateTapi($GLOBALS["testedTeam"]["user"], $GLOBALS["testedTeam"]["pass"]);
        $this->tapiObject->init();
        $this->setCorrectInputParams();
        $data = $this->tapiObject->getData(TRUE);
        
        Assert::true(is_object($data));//returned discussion object        
        Assert::type("int",$data->id);
        Assert::type("string",$data->caption);
        Assert::type("string",$data->description);
        Assert::type("string",$data->readRightName);
        Assert::type("string",$data->writeRightName);
        Assert::type("string",$data->deleteRightName);
        Assert::type("string",$data->stickyRightName);
        Assert::type("bool",$data->publicRead);
        Assert::type("string",$data->status);
        Assert::type("bool",$data->editablePosts);
        Assert::type("int",$data->order);
    }

}

$test = new DiscussionDetailResourceTest($container);
$test->run();
