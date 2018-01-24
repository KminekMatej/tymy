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

class DiscussionPostDeleteResourceTest extends TapiTest {
    
    public function getCacheable() {
        return FALSE;
    }

    public function getJSONEncoding() {
        return TRUE;
    }

    public function getMethod() {
        return \Tapi\RequestMethod::DELETE;
    }

    public function setCorrectInputParams() {
        $this->tapiObject->setId($GLOBALS["testedTeam"]["testDiscussionId"]);
        $this->tapiObject->setPostId(rand(0, 100));
    }
    
    public function testErrorNoId(){
        Assert::exception(function(){$this->tapiObject->init()->getData(TRUE);} , "\Tapi\Exception\APIException", "Discussion ID is missing");
        Assert::exception(function(){$this->tapiObject->init()->setId($GLOBALS["testedTeam"]["testDiscussionId"])->getData(TRUE);} , "\Tapi\Exception\APIException", "Post ID is missing");
    }

    public function testPerformSuccess() {
        //delete test is performed on create object (CRUD collaboration)
    }

}

$test = new DiscussionPostDeleteResourceTest($container);
$test->run();
