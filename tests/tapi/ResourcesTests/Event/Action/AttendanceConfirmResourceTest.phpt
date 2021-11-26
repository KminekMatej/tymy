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

class AttendanceConfirmResourceTest extends TapiTest {
    
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
        $this->tapiObject->setId($GLOBALS["testedTeam"]["testEventId"])->setPostStatuses([
            [
                "userId" => $this->user->getId(),
                "eventId" => $GLOBALS["testedTeam"]["testEventId"],
                "postStatus" => "YES",
            ]
        ]);
    }

    public function testErrorNoId(){
        Assert::exception(function(){$this->tapiObject->init()->getData(TRUE);} , "\Tapi\Exception\APIException", "Event ID is missing");
    }

    public function testErrorNoObject(){
        Assert::exception(function(){$this->tapiObject->init()->setId($GLOBALS["testedTeam"]["testEventId"])->getData(TRUE);} , "\Tapi\Exception\APIException", "Statuses object is missing");
    }

    public function testPerformSuccess() {
        $this->authenticateTapi($GLOBALS["testedTeam"]["user_admin"], $GLOBALS["testedTeam"]["pass_admin"]);
        $this->tapiObject->init();
        $this->setCorrectInputParams();
        $data = $this->tapiObject->getData(TRUE);
    }

}

$test = new AttendanceConfirmResourceTest($container);
$test->run();
