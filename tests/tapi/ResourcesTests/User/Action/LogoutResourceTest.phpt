<?php

namespace Test\Tapi;

use Tester\Environment;
use Tymy\Module\Core\Model\RequestMethod;

$container = require substr(__DIR__, 0, strpos(__DIR__, "tests/tapi")) . "tests/bootstrap.php";

if (in_array(basename(__FILE__, '.phpt') , $GLOBALS["testedTeam"]["skips"])) {
    Environment::skip('Test skipped as set in config file.');
}

class LogoutResourceTest extends TapiTest {
    
    public function getCacheable() {
        return FALSE;
    }

    public function getJSONEncoding() {
        return TRUE;
    }

    public function getMethod() {
        return RequestMethod::GET;
    }
    
    public function setCorrectInputParams() {
        //nothing to set
    }

    public function testPerformSuccess() {
        parent::getPerformSuccessData();
    }
}

$test = new LogoutResourceTest($container);
$test->run();
