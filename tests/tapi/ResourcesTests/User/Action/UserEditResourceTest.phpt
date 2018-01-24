<?php

namespace Test\Tapi;
use Tapi\Exception\APIException;

use Nette;
use Nette\Application\Request;
use Tester\Assert;
use Tester\Environment;

$container = require substr(__DIR__, 0, strpos(__DIR__, "tests/tapi")) . "tests/bootstrap.php";

if (in_array(basename(__FILE__, '.phpt') , $GLOBALS["testedTeam"]["skips"])) {
    Environment::skip('Test skipped as set in config file.');
}

class UserEditResourceTest extends TapiTest {
    
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
        $this->tapiObject->setId($GLOBALS["testedTeam"]["testPollId"])->setUser($this->mockUser());
    }
    
    public function testErrors() {
        Assert::exception(function() {
            $this->tapiObject->init()->getData(TRUE);
        }, "\Tapi\Exception\APIException", "User ID not set");
        Assert::exception(function() {
            $this->tapiObject->init()->setId($GLOBALS["testedTeam"]["testUserId"])->getData(TRUE);
        }, "\Tapi\Exception\APIException", "User object not set");
    }

    public function testPerformSuccess() {
        $this->authenticateTapi($GLOBALS["testedTeam"]["user_admin"], $GLOBALS["testedTeam"]["pass_admin"]);
        $this->tapiObject->init();
        $this->setCorrectInputParams();
        $this->tapiObject->getData(TRUE);
    }

    private function mockUser(){
        return ["callName" => "Autocreator" . rand(100,200)];
    }
}

$test = new UserEditResourceTest($container);
$test->run();
