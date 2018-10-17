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

class DiscussionEditResourceTest extends TapiTest {
    
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
        $this->tapiObject->setId(rand(100,200));
        $this->tapiObject->setDiscussion((object)["caption" => "Autotest edited discussion " . md5(rand(0, 100))]);
    }
    
    public function testErrorNoId(){
        Assert::exception(function(){$this->tapiObject->init()->getData(TRUE);} , "\Tapi\Exception\APIException", "Discussion ID is missing");
    }

    public function testErrorNoObject(){
        Assert::exception(function(){$this->tapiObject->init()->setId(rand(100,200))->getData(TRUE);} , "\Tapi\Exception\APIException", "Discussion object is missing");
    }

    public function testPerformSuccess() {
        //operational tests are performed on mother object (CRUD collaboration)
    }

}

$test = new DiscussionEditResourceTest($container);
$test->run();
