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

class UserDetailResourceTest extends TapiTest {
    
    public function getCacheable() {
        return TRUE;
    }

    public function getJSONEncoding() {
        return TRUE;
    }

    public function getMethod() {
        return \Tymy\Module\Core\Model\RequestMethod::GET;
    }

    public function setCorrectInputParams() {
        $this->tapiObject->setId($GLOBALS["testedTeam"]["testUserId"]);
    }
    
    public function testErrorNoId(){
        Assert::exception(function(){$this->tapiObject->init()->getData(TRUE);} , "\Tapi\Exception\APIException", "User ID is missing");
    }

    public function testItemNotFound(){
        $this->authenticateTapi($GLOBALS["testedTeam"]["user"], $GLOBALS["testedTeam"]["pass"]);
        Assert::exception(function(){$this->tapiObject->init()->setId(3190)->getData(TRUE);} , "\Tapi\Exception\APINotFoundException", "Záznam nenalezen");
    }

    public function testPerformSuccess() {
        $data = parent::getPerformSuccessData();
        
        Assert::true(is_object($data));//returned user object
        Assert::type("int",$data->id);
        Assert::true($data->id > 0);
        Assert::type("string",$data->login);
        Assert::type("bool",$data->canLogin);
        Assert::type("bool",$data->canEditCallName);
        Assert::type("string",$data->lastLogin);
        Assert::same(1, preg_match_all($GLOBALS["dateRegex"], $data->lastLogin)); //timezone correction check
        Assert::type("string",$data->status);
        Assert::true(in_array($data->status, ["PLAYER", "MEMBER", "SICK"]));
        if (property_exists($data, "roles")) {
            Assert::type("array", $data->roles);
            foreach ($data->roles as $role) {
                Assert::type("string", $role);
            }
        }
        Assert::type("string",$data->firstName);
        Assert::type("string",$data->lastName);
        Assert::type("string",$data->callName);
        Assert::type("string",$data->language);
        Assert::type("string",$data->email);
        Assert::type("string",$data->jerseyNumber);
        if(property_exists($data, "gender"))
            Assert::type("string",$data->gender);
        Assert::type("string",$data->street);
        Assert::type("string",$data->city);
        Assert::type("string",$data->zipCode);
        Assert::type("string",$data->phone);
        Assert::type("string",$data->phone2);
        if(property_exists($data, "birthDate"))
            Assert::type("string",$data->birthDate);
        Assert::type("int",$data->nameDayMonth);
        Assert::type("int",$data->nameDayDay);
        Assert::type("string",$data->pictureUrl);
        Assert::type("string",$data->fullName);
        Assert::type("string",$data->displayName);
        Assert::type("string",$data->webName);
        Assert::type("int",$data->errCnt);
        Assert::true($data->errCnt>= 0);
        Assert::type("array",$data->errFls);
        foreach ($data->errFls as $errF) {
            Assert::type("string",$errF);
        }
    }
    
}

$test = new UserDetailResourceTest($container);
$test->run();
