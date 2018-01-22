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

class EventTypeListResourceTest extends TapiTest {
    
    public function getCacheable() {
        return TRUE;
    }

    public function getJSONEncoding() {
        return TRUE;
    }

    public function getMethod() {
        return \Tapi\RequestMethod::GET;
    }

    public function setCorrectInputParams() {
        // nothing to set
    }
    
    public function testPerformSuccess() {
        $data = parent::getPerformSuccessData();
        
        foreach ($data as $key => $evt) {
            Assert::equal($key, $evt->code); //check that key equals evt->code
            Assert::true(is_object($evt));
            Assert::type("int",$evt->id);
            Assert::true($evt->id > 0);
            Assert::type("string",$evt->code);
            Assert::type("string",$evt->caption);
            Assert::type("int",$evt->preStatusSetId);
            Assert::true($evt->preStatusSetId > 0);
            Assert::type("int",$evt->postStatusSetId);
            Assert::true($evt->postStatusSetId > 0);
            Assert::type("array",$evt->preStatusSet);
            foreach ($evt->preStatusSet as $key_pre => $evtSS) {
                Assert::equal($key_pre, $evtSS->code); //check that key equals code
                Assert::type("int",$evtSS->id);
                Assert::true($evtSS->id > 0);
                Assert::type("string",$evtSS->code);
                Assert::type("string",$evtSS->caption);
            }
            Assert::type("array",$evt->postStatusSet);
            foreach ($evt->postStatusSet as $key_post => $evtSS) {
                Assert::equal($key_post, $evtSS->code); //check that key equals code
                Assert::type("int",$evtSS->id);
                Assert::true($evtSS->id > 0);
                Assert::type("string",$evtSS->code);
                Assert::type("string",$evtSS->caption);
            }
        }
    }

}

$test = new EventTypeListResourceTest($container);
$test->run();
