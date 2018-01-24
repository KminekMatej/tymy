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
        $this->tapiObject->setDiscussion((object)["caption" => "Autotest created discussion " . md5(rand(0, 100))]);
    }
    
    public function testErrorNoObject(){
        Assert::exception(function(){$this->tapiObject->init()->getData(TRUE);} , "\Tapi\Exception\APIException", "Discussion object not set");
    }

    public function testPerformSuccess() {
        $this->authenticateTapi($GLOBALS["testedTeam"]["user_admin"], $GLOBALS["testedTeam"]["pass_admin"]);
        $this->tapiObject->init();
        $this->setCorrectInputParams();
        $data = $this->tapiObject->getData(TRUE);
        $deleter = $this->container->getByType("Tapi\DiscussionDeleteResource");
        $deleter->init()->setId($data->id)->perform();
    }

}

$test = new DiscussionCreateResourceTest($container);
$test->run();
