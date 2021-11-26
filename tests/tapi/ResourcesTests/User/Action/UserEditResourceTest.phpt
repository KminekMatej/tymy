<?php

namespace Test\Tapi;

use Tester\Assert;
use Tester\Environment;
use Tymy\Module\Core\Model\RequestMethod;

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
        return RequestMethod::PUT;
    }
    
    public function setCorrectInputParams() {
        $this->tapiObject->setId($GLOBALS["testedTeam"]["testUserId"])->setUser($this->mockUser());
    }
    
    public function testErrors() {
        Assert::exception(function() {
            $this->tapiObject->init()->getData(TRUE);
        }, "\Tapi\Exception\APIException", "User ID is missing");
        Assert::exception(function() {
            $this->tapiObject->init()->setId($GLOBALS["testedTeam"]["testUserId"])->getData(TRUE);
        }, "\Tapi\Exception\APIException", "User object is missing");
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
