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

class DiscussionPostCreateResourceTest extends TapiTest {
    
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
        $this->tapiObject->setId($GLOBALS["testedTeam"]["testDiscussionId"]);
        $this->tapiObject->setPost("Autotest created post " . md5(rand(0, 100)));
    }
    
    public function testErrorNoId(){
        Assert::exception(function(){$this->tapiObject->init()->getData(TRUE);} , "\Tapi\Exception\APIException", "Discussion ID is missing");
    }

    public function testErrorNoPost(){
        Assert::exception(function(){$this->tapiObject->init()->setId(rand(0, 100))->getData(TRUE);} , "\Tapi\Exception\APIException", "Post is missing");
    }

    public function testPerformSuccess() {
        $this->authenticateTapi($GLOBALS["testedTeam"]["user_admin"], $GLOBALS["testedTeam"]["pass_admin"]);
        $this->tapiObject->init();
        $this->setCorrectInputParams();
        $data = $this->tapiObject->getData(TRUE);
        $deleter = $this->container->getByType("Tapi\DiscussionPostDeleteResource");
        $deleter->init()->setId($GLOBALS["testedTeam"]["testDiscussionId"])->setPostId($data->id)->perform();
    }

}

$test = new DiscussionPostCreateResourceTest($container);
$test->run();
