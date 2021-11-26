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

class UsersLiveResourceTest extends TapiTest {
    
    public function getCacheable() {
        return FALSE;
    }

    public function getJSONEncoding() {
        return TRUE;
    }

    public function getMethod() {
        return \Tymy\Module\Core\Model\RequestMethod::GET;
    }

    public function setCorrectInputParams() {
        //nothing to set
    }

    public function testPerformSuccess() {
        $data = parent::getPerformSuccessData();
        
        Assert::type("array", $data); //returned event object
        
        foreach ($data as $u) {
            Assert::true(is_object($u));
            Assert::type("int", $u->id);
            Assert::type("string", $u->login);
            Assert::type("string", $u->callName);
            Assert::type("string", $u->pictureUrl);
            Assert::type("string", $u->gender);
            Assert::type("string", $u->status);
            Assert::true(in_array($u->status, ["PLAYER", "MEMBER", "SICK", "DELETED", "INIT"]));
            Assert::type("string", $u->webName);
        }
    }
    
}

$test = new UsersLiveResourceTest($container);
$test->run();
