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

class PollCreateResourceTest extends TapiTest {
    
    public function getCacheable() {
        return FALSE;
    }

    public function getJSONEncoding() {
        return TRUE;
    }

    public function getMethod() {
        return \Tymy\Module\Core\Model\RequestMethod::POST;
    }
    
    public function setCorrectInputParams() {
        $this->tapiObject->setPoll($this->mockPoll());
    }
    
    public function testErrors() {
        Assert::exception(function() {
            $this->tapiObject->init()->getData(TRUE);
        }, "\Tapi\Exception\APIException", "Poll object is missing");
    }

    public function testPerformSuccess() {
        $this->authenticateTapi($GLOBALS["testedTeam"]["user_admin"], $GLOBALS["testedTeam"]["pass_admin"]);
        $this->tapiObject->init();
        $this->setCorrectInputParams();
        $data = $this->tapiObject->getData(TRUE);
        //edit
        $editor = $this->container->getByType("Tapi\PollEditResource");
        $editor->init()->setId($data->id)->setPoll(["caption"=>"Autotest " . rand(0, 100)])->perform();
        //delete
        $deleter = $this->container->getByType("Tapi\PollDeleteResource");
        $deleter->init()->setId($data->id)->perform();
    }

    private function mockPoll(){
        return ["caption"=>"Autotest " . rand(0, 100)];
    }
    
}

$test = new PollCreateResourceTest($container);
$test->run();
