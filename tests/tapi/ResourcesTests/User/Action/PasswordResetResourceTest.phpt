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

class PasswordResetResourceTest extends TapiTest {
    
    public function getCacheable() {
        return FALSE;
    }

    public function getJSONEncoding() {
        return TRUE;
    }

    public function getMethod() {
        return \Tapi\RequestMethod::GET;
    }
    
    public function setCorrectInputParams() {
        $this->tapiObject->setCode("ABCDEF");
    }
    
    public function testErrors() {
        Assert::exception(function() {
            $this->tapiObject->init()->getData(TRUE);
        }, "\Tapi\Exception\APIException", "Code is missing");
    }

    public function testPerformSuccess() {
        //cannot be performed, due to unknown code
    }
}

$test = new PasswordResetResourceTest($container);
$test->run();
