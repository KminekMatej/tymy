<?php

namespace Test\Tapi;
use Tester\Assert;
use Tester\Environment;

$container = require substr(__DIR__, 0, strpos(__DIR__, "tests/tapi")) . "tests/bootstrap.php";

if (in_array(basename(__FILE__, '.phpt') , $GLOBALS["testedTeam"]["skips"])) {
    Environment::skip('Test skipped as set in config file.');
}

Environment::lock('tapi', substr(__DIR__, 0, strpos(__DIR__, "tests/lockdir"))); //belong to the group of tests which should not run paralelly

class UserCreateResourceTest extends TapiTest {
    
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
        $this->tapiObject->setUser($this->mockUser());
    }
    
    public function testErrors() {
        Assert::exception(function() {
            $this->tapiObject->init()->getData(TRUE);
        }, "\Tapi\Exception\APIException", "User object is missing");
    }

    public function testPerformSuccess() {
        $this->authenticateTapi($GLOBALS["testedTeam"]["user_admin"], $GLOBALS["testedTeam"]["pass_admin"]);
        $this->tapiObject->init();
        $this->setCorrectInputParams();
        $data = $this->tapiObject->getData(TRUE);
        //edit
        $editor = $this->container->getByType("Tapi\UserEditResource");
        $editor->init()->setId($data->id)->setUser($this->mockUser())->perform();
        //delete
        $userDeleter = $this->container->getByType("Tapi\UserDeleteResource");
        $userDeleter->setId($data->id)->perform();
    }

    private function mockUser(){
        return ["callName" => "Autocreator" . rand(100,200),"login" => "Autocreator" . rand(100,200)];
    }
}

$test = new UserCreateResourceTest($container);
$test->run();