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

class PasswordLostResourceTest extends TapiTest {
    
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
        $hostname = "autotester_host";
        $callback = "http://www.autotest.tymy.cz/?code=%s";
        $this->tapiObject->setMail($GLOBALS["testedTeam"]["userMail"])->setHostname($hostname)->setCallbackUri($callback);
    }
    
    public function testErrors() {
        Assert::exception(function() {
            $this->tapiObject->init()->getData(TRUE);
        }, "\Tapi\Exception\APIException", "E-mail not set");
        Assert::exception(function() {
            $this->tapiObject->init()->setMail($GLOBALS["testedTeam"]["userMail"])->getData(TRUE);
        }, "\Tapi\Exception\APIException", "Hostname not set");
        Assert::exception(function() {
            $this->tapiObject->init()->setMail($GLOBALS["testedTeam"]["userMail"])->setHostname("autotester_host")->getData(TRUE);
        }, "\Tapi\Exception\APIException", "Callback not set");
    }

    public function testPerformSuccess() {
        if(!$GLOBALS["testedTeam"]["invasive"]){
            return null;
        }
        parent::getPerformSuccessData();
    }

}

$test = new PasswordLostResourceTest($container);
$test->run();
