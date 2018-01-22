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

class DiscussionPostEditResourceTest extends TapiTest {
    
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
        $this->tapiObject->setId($GLOBALS["testedTeam"]["testDiscussionId"]);
        $this->tapiObject->setPostId(rand(0, 100));
        $this->tapiObject->setPost("Autotest edited post " . md5(rand(0, 100)));
    }
    
    public function testErrorNoId(){
        Assert::exception(function(){$this->tapiObject->init()->getData(TRUE);} , "\Tapi\Exception\APIException", "Discussion ID not set");
    }

    public function testErrorNoPostId(){
        Assert::exception(function(){$this->tapiObject->init()->setId($GLOBALS["testedTeam"]["testDiscussionId"])->getData(TRUE);} , "\Tapi\Exception\APIException", "Post ID not set");
    }

    public function testErrorNothingSet(){
        Assert::exception(function(){$this->tapiObject->init()->setId($GLOBALS["testedTeam"]["testDiscussionId"])->setPostId(rand(0, 100))->getData(TRUE);} , "\Tapi\Exception\APIException", "Nothing to update");
    }

    public function testPerformSuccess() {
        //operational tests are performed on mother object (CRUD collaboration)
    }

}

$test = new DiscussionPostEditResourceTest($container);
$test->run();
