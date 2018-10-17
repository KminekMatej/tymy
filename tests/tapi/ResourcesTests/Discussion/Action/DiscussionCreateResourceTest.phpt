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

Environment::lock('tapi', substr(__DIR__, 0, strpos(__DIR__, "tests/lockdir"))); //belong to the group of tests which should not run paralelly

class DiscussionCreateResourceTest extends TapiTest {
    
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
        $this->tapiObject->setDiscussion($this->mockDiscussion());
    }
    
    public function testErrorNoObject(){
        Assert::exception(function(){$this->tapiObject->init()->getData(TRUE);} , "\Tapi\Exception\APIException", "Discussion object is missing");
    }

    public function testPerformSuccess() {
        $this->authenticateTapi($GLOBALS["testedTeam"]["user_admin"], $GLOBALS["testedTeam"]["pass_admin"]);
        $this->tapiObject->init();
        $this->setCorrectInputParams();
        $data = $this->tapiObject->getData(TRUE);
        //edit
        $editor = $this->container->getByType("Tapi\DiscussionEditResource");
        $editor->init()->setId($data->id)->setDiscussion($this->mockDiscussion())->perform();
        //delete
        $deleter = $this->container->getByType("Tapi\DiscussionDeleteResource");
        $deleter->init()->setId($data->id)->perform();
    }
    
    private function mockDiscussion(){
        return (object)["caption" => "Autotest created discussion " . md5(rand(0, 100))];
    }

}

$test = new DiscussionCreateResourceTest($container);
$test->run();
