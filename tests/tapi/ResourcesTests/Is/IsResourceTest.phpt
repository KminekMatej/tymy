<?php

namespace Test\Tapi;

use Tapi\RequestMethod;
use Tester\Assert;
use Tester\Environment;

$container = require substr(__DIR__, 0, strpos(__DIR__, "tests/tapi")) . "tests/bootstrap.php";

if (in_array(basename(__FILE__, '.phpt') , $GLOBALS["testedTeam"]["skips"])) {
    Environment::skip('Test skipped as set in config file.');
}

class IsResourceTest extends TapiTest {
    
    public function getCacheable() {
        return TRUE;
    }

    public function getJSONEncoding() {
        return TRUE;
    }

    public function getMethod() {
        return RequestMethod::GET;
    }

    public function setCorrectInputParams() {
        $this->tapiObject->setId($GLOBALS["testedTeam"]["testEventId"]);
    }
    
    public function testPerformSuccess() {
        $data = $this->tapiObject->getData(TRUE);
        
        Assert::true(is_object($data));
        Assert::type("string", $data->sysName);
        Assert::type("string", $data->name);
        Assert::type("string", $data->defaultLangugeCode);
        Assert::type("array", $data->languages);
    }

}

$test = new IsResourceTest($container);
$test->run();
