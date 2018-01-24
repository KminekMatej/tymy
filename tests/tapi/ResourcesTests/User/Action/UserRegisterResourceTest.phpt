<?php

namespace Test\Tapi;
use Tapi\UserRegisterResource;
use Tester\Assert;
use Tester\Environment;

$container = require substr(__DIR__, 0, strpos(__DIR__, "tests/tapi")) . "tests/bootstrap.php";

if (in_array(basename(__FILE__, '.phpt') , $GLOBALS["testedTeam"]["skips"])) {
    Environment::skip('Test skipped as set in config file.');
}

Environment::lock('tapi', substr(__DIR__, 0, strpos(__DIR__, "tests/lockdir"))); //belong to the group of tests which should not run paralelly

class UserRegisterResourceTest extends TapiTest {
    
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
        $this->tapiObject->setLogin($this->mockLogin())->setPassword($this->mockPwd())->setEmail($this->mockMail());
    }
    
    public function testErrors() {
        Assert::exception(function() {
            $this->tapiObject->init()->getData(TRUE);
        }, "\Tapi\Exception\APIException", "Login not set");
        Assert::exception(function() {
            $this->tapiObject->init()->setLogin($this->mockLogin())->getData(TRUE);
        }, "\Tapi\Exception\APIException", "Password not set");
        Assert::exception(function() {
            $this->tapiObject->init()->setLogin($this->mockLogin())->setPassword($this->mockPwd())->getData(TRUE);
        }, "\Tapi\Exception\APIException", "Email not set");
    }

    public function testPerformSuccess() {
        if(!$GLOBALS["testedTeam"]["invasive"]){
            return null;
        }
        $data = parent::getPerformSuccessData();
        
        Assert::true(is_object($data));//returned user object
        Assert::type("int",$data->id);
        Assert::true($data->id > 0);
        Assert::type("string",$data->login);
        Assert::type("bool",$data->canLogin);
        Assert::same(1, preg_match_all($GLOBALS["dateRegex"], $data->createdAt)); //timezone correction check
        Assert::type("string",$data->status);
        Assert::same("INIT",$data->status);
        Assert::type("string",$data->callName);
        Assert::type("string",$data->email);
        Assert::type("string",$data->jerseyNumber);
        Assert::type("string",$data->gender);
        Assert::type("string",$data->note);
        Assert::type("string",$data->pictureUrl);
        Assert::type("string",$data->displayName);
        
        $this->authenticateTapi($GLOBALS["testedTeam"]["user_admin"], $GLOBALS["testedTeam"]["pass_admin"]);
        $userDeleter = $this->container->getByType("Tapi\UserDeleteResource");
        $userDeleter->setId($data->id)->perform();
    }

    private function mockLogin(){
        return "Autoregister" . rand(0, 100);
    }

    private function mockPwd(){
        return md5(rand(0, 100));
    }

    private function mockMail(){
        return "test@tester.com";
    }
    
    protected function getTapiObject(){
        return new UserRegisterResource($this->supplier, $this->tapiService);
    }
}

$test = new UserRegisterResourceTest($container);
$test->run();
